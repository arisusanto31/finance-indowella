<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

        return $this->hasMany(DetailKartuInvoice::class, 'sales_order_id', 'id');
    }

    public function parent()
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }


    public function getTotalKartu()
    {
        $kartus = collect($this->detailKartuInvoices)->map(function ($val) {
            $val['code_group_name'] = $val->journal ? $val->journal->chartAccount->name : null;
            if ($val->journal) {
                if($val->journal->code_group >200000){
                    $val['total'] = $val->journal->amount_kredit-$val->journal->amount_debet;
                }else{
                    $val['total'] = $val->journal->amount_debet-$val->journal->amount_kredit;
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


        if (array_key_exists('Uang Muka Penjualan', $total)) {
            $totalDP = $total['Uang Muka Penjualan'];
            if ($totalDP >= $this->total_price) {
                $this->status_payment = "DP LUNAS";
            } else if ($totalDP > 0) {
                $prosen = round($totalDP / $this->total_price * 100);
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
}
