<?php

namespace App\Models;

use App\Http\Controllers\InvoiceSaleController;
use App\Http\Controllers\JournalController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class SalesOrder extends Model
{
    //
    protected $table = 'sales_orders';
    protected $fillable = [
        'book_journal_id',
        'sales_order_number',
        'toko_id',
        'customer_id',
        'total_price',
        'status',
        'reference_id',
        'reference_type',
        'ref_akun_cash_kind_name',
        'created_at',
        'is_ppn',
        'total_ppn_k',
    ];

    public function reference()
    {
        return $this->morphTo();
    }
    public function getReference()
    {
        if ($this->reference_type && $this->reference_id) {
            return $this->reference;
        }
        return null;
    }
    public function details()
    {
        return $this->hasMany(SalesOrderDetail::class, 'sales_order_number', 'sales_order_number');
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
    public function stock()
    {
        return $this->belongsTo(Stock::class, 'stock_id', 'id');
    }

    public function detailKartuInvoices()
    {

        return $this->hasMany(DetailKartuInvoice::class, 'sales_order_number', 'sales_order_number');
    }

    public function parent()
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }

    protected static function booted()
    {
        static::addGlobalScope('journal', function ($query) {
            $from = $query->getQuery()->from ?? 'sales_orders'; // untuk dukung alias `j` kalau pakai from('journals as j')
            if (Str::contains($from, ' as ')) {
                [$table, $alias] = explode(' as ', $from);
                $alias = trim($alias);
            } else {
                $alias = $from;
            }

            $query->where(function ($q) use ($alias) {
                $q->whereNull("{$alias}.book_journal_id")
                    ->orWhere("{$alias}.book_journal_id", bookID());
            });
        });
    }


    public function updateReadyStock()
    {

        try {
            $readyStock = 1;
            $reference = $this->getReference();
            if ($reference) {
                $deliveryAt = $reference->delivery_at;
                if (!$deliveryAt) {
                    $deliveryAt = $this->created_at;
                }
            } else {
                $deliveryAt = $this->created_at;
            }

            //nah mari kita analisa semua stocknya berdasarkan tanggal delivery at ini
            $allKonversi = StockUnit::whereIn('stock_id', $this->details->pluck('stock_id'))->get()->groupBy('stock_id')->map(function ($item) {
                return collect($item)->pluck('konversi', 'unit')->all();
            })->all();


            foreach ($this->details as $detail) {
                $stock = $detail->stock;
                $indexDelivery = createCarbon($deliveryAt)->format('ymdHis000');
                $lastStock = KartuStock::where('stock_id', $stock->id)->where('index_date', '<=', $indexDelivery)->orderBy('index_date', 'desc')->first();
                $saldoStock = 0;
                if ($lastStock) {
                    $saldoStock = floatval($lastStock->saldo_qty_backend)    / floatval($allKonversi[$stock->id][$detail->unit]);
                    info('stock ' . $stock->name . ', saldo stock ' . $saldoStock . ', qty order ' . $detail->quantity);
                    if ($saldoStock < $detail->quantity) {
                        $readyStock = 0;
                        $detail->is_ready_stock = 0;
                        $detail->save();
                    } else {
                        $detail->is_ready_stock = 1;
                        $detail->save();
                    }
                } else {
                    info('stock ' . $stock->name . ', saldo stock ' . $saldoStock . ', qty order ' . $detail->quantity);
                    $readyStock = 0;
                    $detail->is_ready_stock = 0;
                    $detail->save();
                }
            }
            $this->is_ready_stock = $readyStock;
            $this->save();
            return [
                'status' => 1,
                'msg' => 'ready stock updated'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 0,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function findDateReadyStock($allConversion)
    {
        $so = $this;
        $maxDate = [];
        foreach ($so->details as $detail) {
            if ($detail->is_ready_stock == 0) {
                //kita cari stock itu ready pertanggal apa bung.
                $qtyJualBackend = $detail->quantity * ($allConversion[$detail->stock_id][$detail->unit] ?? 1);
                $indexDate = createCarbon($so->created_at)->format('ymdHis000');
                $lastDate = KartuStock::where('index_date', '>', $indexDate)->where('stock_id', $detail->stock_id)
                    ->where('saldo_qty_backend', '>', $qtyJualBackend)->orderBy('index_date', 'asc')
                    ->first();
                if ($lastDate) {
                    $maxDate[] = createCarbon($lastDate->created_at)->addDay()->format('Y-m-d') . ' ' . createCarbon($detail->created_at)->format('H:i:s');
                }
            }
        }
        //marikita ambil yang paling lambat date nya 
        if (!empty($maxDate)) {
            $themaxDate = collect($maxDate)->max();
            $so->created_at = $themaxDate;
            $so->save();
            foreach ($so->details as $detail) {
                $detail->created_at = $themaxDate;
                $detail->save();
            }
            $so->updateReadyStock();
            return [
                'status' => 1,
                'msg' => 'sales order ' . $so->sales_order_number . ' updated to ready stock at ' . $themaxDate
            ];
        } else {
            return [
                'status' => 0,
                'msg' => 'sales order ' . $so->sales_order_number . ' is not ready stock but no max date found'
            ];
        }
    }

    function info($msg)
    {
        info($msg);
    }
    public function getTotalKartu()
    {
        $kartus = collect($this->detailKartuInvoices)->map(function ($val) {
            $val['code_group_name'] = $val->journal ? $val->journal->chartAccount->name : null;
            if ($val->journal) {
                if ($val->journal->code_group > 200000) {
                    $val['total'] = $val->journal->amount_kredit - $val->journal->amount_debet;
                } else {
                    $val['total'] = $val->journal->amount_debet - $val->journal->amount_kredit;
                }
            } else {
                $kartu = $val->kartu_type::find($val->kartu_id);
                if (isset($kartu->amount)) {
                    $val['total'] = $kartu->amount;
                } else if (isset($kartu->amount_debet)) {
                    $val['total'] = $kartu->amount_debet - $kartu->amount_kredit;
                } else if (isset($kartu->total_price)) {
                    $val['total'] = $kartu->total_price;
                } else if (isset($kartu->mutasi_rupiah_total)) {
                    $val['total'] = $kartu->mutasi_rupiah_total;
                }
            }

            return $val;
        })->groupBy('code_group_name')->map(function ($val) {
            return $val->sum('total');
        })->all();

        return $kartus ?? [];
    }

    // public function updateJam($countMax=0){
    //     if($countMax==0){
    //         //cari tau dulu countmax sebenarnya PPl

    //     }
    //     $maxdate= SalesOrder::whereDate('created_at',createCarbon($this->created_at)->format('Y-m-d'))->max('created_at');


    // }

    public function getTotalPosKartu()
    {
        $kartus = collect($this->detailKartuInvoices)->map(function ($val) {
            $val['code_group_name'] = $val->journal ? $val->journal->chartAccount->name : null;
            $val['pos'] = 'unknown';
            if ($val->journal) {
                if ($val->journal->code_group > 200000) {
                    $val['total'] = $val->journal->amount_kredit - $val->journal->amount_debet;
                    $val['pos'] = 'pasiva';
                } else {
                    $val['total'] = $val->journal->amount_debet - $val->journal->amount_kredit;
                    $val['pos'] = 'aktiva';
                }
            } else {
                if ($val->kartuType && $val->kartu_id) {
                    $kartu = $val->kartu_type::find($val->kartu_id);
                    if (isset($kartu->amount)) {
                        $val['total'] = $kartu->amount;
                    } else if (isset($kartu->amount_debet)) {
                        $val['total'] = $kartu->amount_debet - $kartu->amount_kredit;
                    } else if (isset($kartu->total_price)) {
                        $val['total'] = $kartu->total_price;
                    } else if (isset($kartu->mutasi_rupiah_total)) {
                        $val['total'] = $kartu->mutasi_rupiah_total;
                    }
                } else {
                    $val['total'] = 0;
                }
            }
            return $val;
        })->groupBy('pos')->map(function ($vals) {
            return collect($vals)->groupBy('code_group_name')->map(function ($val) {
                return collect($val)->sum('total');
            });
        })->all();

        return $kartus ?? [];
    }
    public function getAllKartu()
    {
        $kartus = collect($this->detailKartuInvoices)->map(function ($val) {
            $val['type_kartu'] = $val->kartu_type ? explode('\\', $val->kartu_type)[2] : 'Kartu lain-lain';
            $val['code_group_name'] = $val->journal->chartAccount->name;
            $val['type_flow'] = $val->journal->amount_debet > 0 ? 'debet' : 'kredit';
            $val['date'] = $val->journal->created_at->format('Y-m-d H:i:s');
            $val['journal_number'] = $val->journal->journal_number;
            return $val;
        })->groupBy('type_kartu')->map(function ($vals) {
            return $vals->groupBy('type_flow')->all();
        })->all();


        return $kartus ?? [];
    }

    public function updateStatus()
    {
        $total = collect($this->getTotalKartu())->map(function ($value, $key) {
            $keys = explode(' ', $key);
            if ($keys[0] == 'Piutang') {
                $thekey = 'Piutang';
            } else if ($keys[0] == 'Penjualan') {
                $thekey = 'Penjualan';
            } else {
                $thekey = $key;
            }
            return ['key' => $thekey, 'value' => $value];
        })->values()->groupBy('key')->map(function ($items) {
            return $items->sum('value');
        })->all();
        $this->status_payment = "draft";
        $this->status_delivery = "draft";
        $this->status = "draft";

        $totalBayar = $this->total_price + $this->total_ppn_k;
        if (array_key_exists('Uang Muka Penjualan', $total)) {
            $totalDP = $total['Uang Muka Penjualan'];
            if ($totalDP >= $totalBayar) {
                $this->status_payment = "DP LUNAS";
            } else if ($totalDP > 0) {
                $prosen = round($totalDP / $totalBayar * 100);
                $this->status_payment = "DP " . $prosen . '%';
            }
            $this->status = "DP";
        }
        if (array_key_exists('Persediaan Dalam Proses', $total)) {
            $totalPersediaan = $total['Persediaan Dalam Proses'];
            if ($totalPersediaan > 0) {
                $this->status_delivery = "Barang diproses";
            } else {
                $this->status_delivery = "Barang Ready";
            }
            $this->status = "Proses";
        }
        if (array_key_exists('Penjualan', $total)) {
            //sudah invoices atau sudah dikirim
            $totalPenjualan = $total['Penjualan'];
            if ($totalPenjualan >= $this->total_price) {
                $this->status_delivery = "terkirim 100%";
                $this->status = "terkirim";
            } else if ($totalPenjualan > 0) {
                $prosen = round($totalPenjualan / $this->total_price * 100);
                $this->status_delivery = "terkirim " . $prosen . '%';
                $this->status = "Kirim";
            }
        }
        if (array_key_exists('Piutang', $total)) {
            $totalPiutang = $total['Piutang'];
            $totalBayar = $this->total_price - $totalPiutang;
            if ($totalBayar >= $this->total_price) {
                $this->status_payment = "LUNAS 100%";
                //sudah invoices atau sudah dikirim
                if ($this->status == "terkirim") {
                    $this->status = "Selesai";
                } else {
                }
            } else if ($totalBayar > 0) {
                $prosen = round($totalBayar / $this->total_price * 100);
                $this->status_payment = "LUNAS " . $prosen . '%';
            }
        } else {
            if (!preg_match('/^DP/', $this->status_payment)) {
                if ($this->status_delivery != "draft") {
                    $this->status_payment = "BELUM BAYAR";
                }
            }
        }

        if ($this->is_final == 1) {
            $this->status = "FINAL";
        } else {
            $this->status = "DRAFT";
        }
        $this->save();
    }

    public function getCodeFix()
    {
        if ($this->index == null) {
            //menandakan bahwa sales order ini belum pernah dapat fix code
            $salesOrder = SalesOrder::where('is_final', 1)->where('customer_id', $this->customer_id)->orderBy('index', 'desc')->first();
            $count = $salesOrder ? $salesOrder->index + 1 : 1;
            $this->index = $count;
            $number = 'SO-' . date('Y') . '-' . toDigit($this->customer_id, 4) . '-' . toDigit($count, 4);
            return $number;
        }
        return $this->sales_order_number;
    }

    public function lunaskanDagang()
    {
       $starttime= microtime(true);
        DB::BeginTransaction();
        try {
            $invoicePack = InvoicePack::where('sales_order_id', $this->id)->first();
            if (!$invoicePack) {
                return [
                    'status' => 0,
                    'msg' => 'Invoice pack tidak ditemukan'
                ];
            }
            info('repairlunas - '.$this->id.'- cari invoice pack '.(microtime(true)-$starttime).' seconds');

            $invoiceNumber = $invoicePack->invoice_number;
            $amount = $invoicePack->total_price + $invoicePack->total_ppn_k;
            $date = $this->created_at;
            $codeBayar = null;
            if ($this->ref_akun_cash_kind_name) {
                $link = LinkReferenceCashKind::where('cash_kind_name', $this->ref_akun_cash_kind_name)->first();
                if ($link) {
                    $codeBayar = $link->code_group;
                }
            } else {
                $toko = Toko::find($this->toko_id);
                if (!$toko) {
                    return [
                        'status' => 0,
                        'msg' => 'Toko tidak ditemukan'
                    ];
                }
                $codeBayar = $toko->default_code_group_kas;
            }
            if (!$codeBayar) {
                throw new \Exception('Kode bayar tidak ditemukan untuk toko ' . $this->toko_id);
            }
            info('repairlunas - '.$this->id.'- cari code bayar '.(microtime(true)-$starttime).' seconds');
            $codeGroupPiutang = 120001;
            $st = InvoiceSaleController::submitBayarSalesInvoice(new Request([
                'invoice_number' => $invoiceNumber,
                'amount' => $amount,
                'date' => $date,
                'codegroup_bayar' => $codeBayar,
                'codegroup_piutang' => $codeGroupPiutang,
            ]), false, false);
            info('repairlunas - '.$this->id.'- submit bayar '.(microtime(true)-$starttime).' seconds');

            if ($st['status'] == 0) {
                return $st;
            }
            $this->updateStatus();
            DB::commit();
            return [
                'status' => 1,
                'msg' => 'Sales order ' . $this->sales_order_number . ' berhasil dilunaskan'
            ];
        } catch (Throwable $th) {
            DB::rollBack();
            return [
                'status' => 0,
                'msg' => $th->getMessage()
            ];
        }
    }

    public function repairPembayaran()
    {
        $start=microtime(true);
        $saleOrder = $this;
        if($this->status_payment=='LUNAS 100%'){
            return true;
        }
        try {

            $invoice = InvoicePack::where('sales_order_id', $saleOrder->id)->first();
            if (!$invoice) {
                throw new \Exception('Invoice tidak ditemukan untuk sales order id ' . $saleOrder->id);
            }
            // $journal = Journal::where('description', 'pelunasan piutang dari invoice ' . $invoice->invoice_number)->first();
            $detail= DetailKartuInvoice::where('sales_order_number',$saleOrder->sales_order_number)
              ->whereBetween('account_code_group',[110000,119999])->first();
            
            info('repair '.$this->id.'- get jurnal on '.(microtime(true)-$start).' seconds');
            if ($detail) {
                $st = JournalController::destroy($detail->journal_id, 1);
                if ($st['status'] == 1) {
                    // info('Pembayaran invoice ' . $invoice->invoice_number . ' berhasil dibatalkan');
                    info('repair '.$this->id.'- destroy jurnal on '.(microtime(true)-$start).' seconds');
                } else {

                    throw new \Exception('Gagal membatalkan pembayaran invoice ' . $invoice->invoice_number . '
            Error: ' . $st['msg']);
                }
            }

            $st = $saleOrder->lunaskanDagang();
            if ($st['status'] == 1) {
                info('repair '.$this->id.'- lunaskan dagang on '.(microtime(true)-$start).' seconds');
                info('Status pelunasan untuk sales order ' . $saleOrder->sales_order_number . ' berhasil diupdate');
                // $this->success();
                return true;
            } else {
                info('Gagal mengupdate status pelunasan untuk sales order ' . $saleOrder->sales_order_number . '
            // Error: ' . $st['msg']);
                // $this->failed();
                 return false;
                }

        
        } catch (\Exception $e) {
            info('Error processing sales order ' . $saleOrder->sales_order_number . ': ' . $e->getMessage());
            // $this->failed();
            return false;
        }
    }
}
