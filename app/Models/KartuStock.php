<?php

namespace App\Models;

use App\Http\Controllers\JournalController;
use App\Traits\HasIndexDate;
use App\Traits\HasModelDetailKartuInvoice;
use App\Traits\HasModelSaldoStock;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class KartuStock extends Model
{
    //

    use HasModelDetailKartuInvoice;
    use HasModelSaldoStock;
    use HasIndexDate;
    protected $table = 'kartu_stocks';
    public $timestamps = true;

    protected static function booted()
    {
        static::addGlobalScope('journal', function ($query) {
            $from = $query->getQuery()->from ?? 'kartu_stocks'; // untuk dukung alias `j` kalau pakai from('journals as j')
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



    public static function create(Request $request)
    {
        $lock = Cache::lock('kartu-stock-' . $request->input('stock_id'), 40);
        try {
            $date = $request->input('date') ?? now();
            kartuStock::proteksiBackdate($date);
            $flow = $request->input('flow');
            $isCustom = $request->input('is_custom_rupiah');
            $kartu = new KartuStock;
            $kartu->stock_id = $request->input('stock_id');
            $kartu->mutasi_qty_backend = $request->input('mutasi_qty_backend');
            $kartu->unit_backend = $request->input('unit_backend');
            $kartu->mutasi_quantity = $request->input('mutasi_quantity');
            $kartu->unit = $request->input('unit');
            $kartu->sales_order_number = $request->input('sales_order_number');
            $kartu->sales_order_id = $request->input('sales_order_id');
            $kartu->purchase_order_number = $request->input('purchase_order_number');
            $kartu->purchase_order_id = $request->input('purchase_order_id');
            $kartu->invoice_pack_number = $request->input('invoice_pack_number');
            $kartu->invoice_pack_id = $request->input('invoice_pack_id');

            $indexDate = KartuStock::getNextIndexDate($date);
            $kartu->index_date = $indexDate;
            $kartu->index_date_group = createCarbon($date)->format('ymdHis');
            //mutasi terakhir sebelum mutasi id yg diinput oleh user
            $lastCard = KartuStock::where('stock_id', $kartu->stock_id)->where('index_date', '<', $indexDate)->orderBy('index_date', 'desc')->first();
            if (!$lastCard) {
                $lastCard = new KartuStock;
                $lastCard->saldo_qty_backend = 0;
                $lastCard->saldo_rupiah_total = 0;
            }

            if ($lastCard->saldo_qty_backend == 0 && $flow == 1) {
                //kalo keluar
                throw new \Exception('tidak ada saldo qty barang ini ');
            }

            if ($isCustom == 0 && $flow == 1) {
                if ($lastCard->saldo_qty_backend == 0) {
                    $rupiahUnit = 0;
                } else {
                    $rupiahUnit = round($lastCard->saldo_rupiah_total / $lastCard->saldo_qty_backend, 2);
                }
                if ($rupiahUnit == 0) {
                    return [
                        'status' => 0,
                        'msg' => 'perhitungan input rupiah tidak valid!,'
                    ];
                }
                //ini kayak hpp gitu. pake defaultnya
                $kartu->mutasi_rupiah_total = $lastCard->saldo_rupiah_total * $kartu->mutasi_qty_backend / $lastCard->saldo_qty_backend;
                $kartu->mutasi_rupiah_on_unit = $rupiahUnit;
            } else {

                $kartu->mutasi_rupiah_on_unit = $request->input('mutasi_rupiah_on_unit') ?? 0;
                $kartu->mutasi_rupiah_total = $request->input('mutasi_rupiah_total') ?? 0;
                if ($kartu->mutasi_rupiah_total == 0) {
                    return [
                        'status' => 0,
                        'msg' => 'input rupiah tidak valid!,'
                    ];
                }
            }
            if ($flow == 1) {
                $kartu->mutasi_qty_backend = moneyMul($kartu->mutasi_qty_backend, -1);
                $kartu->mutasi_quantity = moneyMul($kartu->mutasi_quantity, -1);
                $kartu->mutasi_rupiah_on_unit = moneyMul($kartu->mutasi_rupiah_on_unit, -1);
                $kartu->mutasi_rupiah_total = moneyMul($kartu->mutasi_rupiah_total, -1);
            }
            $kartu->code_group = $request->input('code_group');
            $kartu->code_group_name = $request->input('code_group_name');
            $kartu->book_journal_id = bookID();
            $kartu->saldo_qty_backend = moneyAdd($lastCard->saldo_qty_backend, $kartu->mutasi_qty_backend);
            $kartu->saldo_rupiah_total = moneyAdd($lastCard->saldo_rupiah_total, $kartu->mutasi_rupiah_total);
            if ($kartu->saldo_rupiah_total < 0 || $kartu->saldo_qty_backend < 0) {
                info('kartu stock invalid input, saldo minus jika diinput! '.json_encode($kartu));
                return [
                    'status' => 0,
                    'msg' => 'kartu stock invalid input, saldo minus jika diinput!',
                    'detail'=>$kartu
                ];
            }
            $kartu->save();
            if (self::isBackdate($date)) {
                $kartu->recalculateSaldo();
            }
        } catch (LockTimeoutException $e) {
            info('kartu stock timeout on md' . $request->input('mutation_detail_id'));
            return [
                'status' => 0,
                'msg' => 'kartu stock timeout'
            ];
        } catch (Throwable $th) {
            $lock->release();
            return [
                'status' => 0,
                'msg' => $th->getMessage()
            ];
        } finally {
        }
        $lock->release();
        return [
            'status' => 1,
            'msg' => $kartu
        ];
    }

    public function recalculateSaldo()
    {
        $kartus = KartuStock::where('stock_id', $this->stock_id)->where('index_date', '>', $this->index_date)
            ->orderBy('index_date', 'asc')->get();

        $saldoQty = $this->saldo_qty_backend;
        $saldoRupiah = $this->saldo_rupiah_total;
        foreach ($kartus as $kartu) {

            $saldoQty = moneyAdd($saldoQty, $kartu->mutasi_qty_backend);
            $saldoRupiah = moneyAdd($saldoRupiah, $kartu->mutasi_rupiah_total);
            $kartu->saldo_qty_backend = $saldoQty;
            $kartu->saldo_rupiah_total = $saldoRupiah;
            $kartu->save();
        }
    }
    public static function mutationStore(Request $request, $useTransaction = true)
    {


        if ($useTransaction)
            DB::beginTransaction();
        try {
            $stockid = $request->input('stock_id');
            info('create kartu stock ' . $stockid);
            $qty = format_db($request->input('mutasi_quantity'));
            $date = $request->input('date') ?? now();
            $unit = $request->input('unit');
            $flow = $request->input('flow');
            $codeGroup = $request->input('code_group');
            $chart = ChartAccount::where('code_group', $codeGroup)->first();
            $PONumber = $request->input('purchase_order_number');
            $SONumber = $request->input('sales_order_number');
            $invoiceNumber = $request->input('invoice_pack_number');
            $isOtomatisJurnal = $request->input('is_otomatis_jurnal') == 'on' ? true : false;
            $desc = $request->input('description');
            $lawanCodeGroup = $request->input('lawan_code_group');

            $POID = $SOID = $invID = null;
            if ($PONumber) {
                $PO = PurchaseOrder::where('purchase_order_number', $PONumber)->first();
                $POID = $PO ? $PO->id : null;
            }
            if ($SONumber) {
                $SO = SalesOrder::where('sales_order_number', $SONumber)->first();
                $SOID = $SO ? $SO->id : null;
            }
            if ($invoiceNumber) {
                $inv = InvoicePack::where('invoice_number', $invoiceNumber)->first();
                $invID = $inv ? $inv->id : null;
            }
            if (!$chart) {
                if ($useTransaction)
                    DB::rollBack();
                return [
                    'status' => 0,
                    'msg' => 'code group tidak ditemukan'
                ];
            }

            $codeGroupName = $chart->name;
            if ($request->input('mutasi_rupiah_total'))
                $mutasiRupiahTotal = format_db($request->input('mutasi_rupiah_total'));
            else
                $mutasiRupiahTotal = 0;
            $isCustom = $request->input('is_custom_rupiah');
            info('stockid:' . $stockid);
            info('unit:' . $unit);
            $dataunit = StockUnit::where('stock_id', $stockid)->where('unit', ownucfirst($unit))->first();

            $stock = Stock::find($stockid);
            // info('info stock:' . json_encode($stock));
            if (!$unit) {
                if ($useTransaction)
                    DB::rollBack();
                return [
                    'status' => 0,
                    'msg' => 'unit tidak ditemukan'
                ];
            }
            $qtybackend = $qty * $dataunit->konversi;
            $unitbackend = $stock->unit_backend;

            $mutasiRupiahUnit = money($mutasiRupiahTotal / $qtybackend);
            $st = self::create(new Request([
                'purchase_order_number' => $PONumber,
                'purchase_order_id' => $POID,
                'sales_order_number' => $SONumber,
                'sales_order_id' => $SOID,
                'invoice_pack_number' => $invoiceNumber,
                'invoice_pack_id' => $invID,
                'stock_id' => $stockid,
                'mutasi_qty_backend' => $qtybackend,
                'unit_backend' => $unitbackend,
                'mutasi_quantity' => $qty,
                'unit' => $unit,
                'flow' => $flow,
                'is_custom_rupiah' => $isCustom,
                'mutasi_rupiah_on_unit' => $mutasiRupiahUnit,
                'mutasi_rupiah_total' => $mutasiRupiahTotal,
                'code_group' => $codeGroup,
                'code_group_name' => $codeGroupName,
                'date' => $date,
            ]));

            if ($st['status'] == 0) {
                throw new \Exception($st['msg']);
            }
            $ks = $st['msg'];
            $number = null;
            if ($isOtomatisJurnal) {
                if (!$desc) {
                    throw new \Exception('deskripsi tidak boleh kosong');
                }
                //buat kartu lawan yaa
                $amount = abs($ks->mutasi_rupiah_total);
                if ($flow == 1) {
                    //keluar
                    $codeDebet = $lawanCodeGroup;
                    $codeKredit = $codeGroup;
                } else {
                    $codeDebet = $codeGroup;
                    $codeKredit = $lawanCodeGroup;
                }
                $kredits = [
                    [
                        'code_group' => $codeKredit,
                        'description' => $desc,
                        'amount' => $amount,
                        'reference_id' => null,
                        'reference_type' => null,
                    ],
                ];
                $debets = [
                    [
                        'code_group' => $codeDebet,
                        'description' => $desc,
                        'amount' => $amount,
                        'reference_id' => null,
                        'reference_type' => null,
                    ],
                ];
                $st = JournalController::createBaseJournal(new Request([
                    'kredits' => $kredits,
                    'debets' => $debets,
                    'type' => 'transaction',
                    'date' => $date,
                    'is_backdate' => self::isBackdate($date),
                    'is_auto_generated' => 1,
                    'title' => 'create mutation transaction',
                    'url_try_again' => 'try_again'

                ]), false);
                if ($st['status'] != 1) throw new \Exception($st['msg']);
                $number = $st['journal_number'];
                $journal = Journal::where('journal_number', $number)->whereIn('code_group', [140001, 140002])->first();
                $ks->journal_id = $journal->id;
                $ks->journal_number = $number;
                $ks->save();
                $ks->createDetailKartuInvoice();
            }
        } catch (Throwable $th) {
            if ($useTransaction)
                DB::rollBack();
            return [
                'status' => 0,
                'msg' => $th->getMessage()
            ];
        } finally {
            if ($useTransaction)
                DB::commit();
        }
        return [
            'status' => 1,
            'msg' => $st['msg']
        ];
    }
}
