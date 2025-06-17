<?php

namespace App\Models;

use App\Http\Controllers\JournalController;
use App\Services\LockManager;
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

class KartuBDP extends Model
{
    //
    use HasIndexDate;
    use HasModelSaldoStock;
    use HasModelDetailKartuInvoice;
    protected $table = 'kartu_bdps';
    public $timestamps = true;

    protected static function booted()
    {
        static::addGlobalScope('journal', function ($query) {
            $from = $query->getQuery()->from ?? 'kartu_bdps'; // untuk dukung alias `j` kalau pakai from('journals as j')
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
        info('TRYING UPLOAD ' . json_encode($request->all()));
        $lock = Cache::lock('kartu-bdp-' . $request->input('stock_id'), 40);
        try {
            $date = $request->input('date') ?? now();
            self::proteksiBackdate($date);
            $flow = $request->input('flow');
            $isCustom = $request->input('is_custom_rupiah');
            $kartu = new KartuBDP;
            $kartu->stock_id = $request->input('stock_id');
            $kartu->mutasi_qty_backend = $request->input('mutasi_qty_backend');
            $kartu->unit_backend = $request->input('unit_backend');
            $kartu->mutasi_quantity = ($request->input('mutasi_quantity'));
            $kartu->unit = $request->input('unit');
            $kartu->sales_order_number = $request->input('sales_order_number');
            $kartu->sales_order_id = $request->input('sales_order_id');
            $kartu->invoice_pack_number = $request->input('invoice_pack_number');
            $kartu->invoice_pack_id = $request->input('invoice_pack_id');
            $kartu->custom_stock_name = $request->input('custom_stock_name');

            $kartu->production_number = $request->input('production_number');
            if (!$kartu->production_number) {
                $kartu->production_number = $request->input('sales_order_number');
            }
            $indexDate = self::getNextIndexDate($date);
            $kartu->index_date = $indexDate;
            $kartu->index_date_group = createCarbon($date)->format('ymdHis');

            //mutasi terakhir sebelum mutasi id yg diinput oleh user
            $lastCard = KartuBDP::where('stock_id', $kartu->stock_id)->where('production_number', $kartu->production_number)
                ->where('index_date', '<', $indexDate)->orderBy('index_date', 'desc')->first();
            info('BDP LAST CARD ' . json_encode($lastCard));
            if (!$lastCard) {
                $lastCard = new KartuBDP;
                $lastCard->saldo_qty_backend = 0;
                $lastCard->saldo_rupiah_total = 0;
            }
            if ($lastCard->saldo_qty_backend == 0 && $flow == 1) {
                //kalo keluar
                throw new \Exception('tidak ada saldo qty barang pada BDP nomer ' . $kartu->production_number);
            }
            if ($isCustom == 0 || $flow == 1) {
                //ini ngambil hpp yang lama.
                if ($lastCard->saldo_qty_backend == 0) {
                    $rupiahUnit = 0;
                } else {
                    $rupiahUnit = $lastCard->saldo_rupiah_total / $lastCard->saldo_qty_backend;
                }
                if ($rupiahUnit == 0) {
                    return [
                        'status' => 0,
                        'msg' => 'perhitungan input rupiah tidak valid!,'
                    ];
                }
                $kartu->mutasi_rupiah_on_unit = $rupiahUnit; //ini kayak hpp gitu. pake defaultnya
                $kartu->mutasi_rupiah_total = $lastCard->saldo_rupiah_total * $kartu->mutasi_qty_backend / $lastCard->saldo_qty_backend;
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
            info('BDP KARTU -perhitutngan mutasi berhasil') ;
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
                return [
                    'status' => 0,
                    'msg' => 'invalid input, saldo minus jika diinput!'
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
            $lock->release();
        }
        return [
            'status' => 1,
            'msg' => $kartu
        ];
    }

    public static function mutationStore(Request $request, $useTransaction = true, ?LockManager $lockManager = null)
    {

        if ($useTransaction)
            DB::beginTransaction();
        try {
            $date = $request->input('date') ?? now();
            self::proteksiBackdate($date);
            $desc = $request->input('description');
            $stockid = $request->input('stock_id');
            $qty = format_db($request->input('mutasi_quantity'));
            $unit = $request->input('unit');
            $flow = $request->input('flow');
            $codeGroup = $request->input('code_group');
            $chart = ChartAccount::where('code_group', $codeGroup)->first();
            $customStockName = $request->input('custom_stock_name');
            $SONumber = $request->input('sales_order_number');
            $invoiceNumber = $request->input('invoice_pack_number');
            $productionNumber = $request->input('production_number');
            if (!$productionNumber) {
                $productionNumber = $SONumber;
            }
            $sales = SalesOrder::where('sales_order_number', $SONumber)->first();
            $SOID = $sales ? $sales->id : null;
            $invoice = InvoicePack::where('invoice_number', $invoiceNumber)->first();
            $invID = $invoice ? $invoice->id : null;
            $modeStock = 'normal';
            if (!$stockid && !$customStockName) {
                throw new \Exception('stock id tidak boleh kosong jika custom stock name tidak diisi');
            }
            if (!$stockid && $customStockName) {
                //map stockid custom stock
                $modeStock = 'custom';
                $kartuBDP = KartuBDP::where('production_number', $productionNumber)->where('custom_stock_name', $customStockName)->first();
                if ($kartuBDP) {
                    $stockid = $kartuBDP->stock_id;
                } else {
                    $idCustom = Stock::withoutGlobalScope('journal')->where('name', 'like', 'stock_custom%')->pluck('id')->all();

                    $stockCustomTerpakai = KartuBDP::where('production_number', $productionNumber)->whereIn('stock_id', $idCustom)->pluck('stock_id')->all();
                    $stockid = $idCustom[0];
                    $i = 0;
                    foreach ($stockCustomTerpakai as $stockCustom) {
                        if ($stockCustom == $stockid) {
                            $i++;
                        } else {
                            break;
                        }
                    }
                    $stockid = $idCustom[$i];
                }
            }
            $lawanCodeGroup = $request->input('lawan_code_group');
            if ($lawanCodeGroup == $codeGroup) {
                throw new \Exception('lawan code group tidak boleh sama dengan code group');
            }
            $isOtomatisJurnal = $request->input('is_otomatis_jurnal');

            if (!$chart) {
                if ($useTransaction)
                    DB::rollBack();
                return [
                    'status' => 0,
                    'msg' => 'code group tidak ditemukan'
                ];
            }

            $codeGroupName = $chart->name;
            if ($request->input('mutasi_rupiah_total')) {
                $mutasiRupiahTotal = format_db($request->input('mutasi_rupiah_total'));
                if ($mutasiRupiahTotal == 0) {
                    return [
                        'status' => 0,
                        'msg' => 'input rupiah tidak valid!,'
                    ];
                }
            } else
                $mutasiRupiahTotal = 0;
            $stock = DB::table('stocks')->where('id', $stockid)->first();
            if ($stock->book_journal_id == 0) {
                $modeStock = 'custom';
            }
            $isCustom = $request->input('is_custom_rupiah');
            if ($modeStock == 'normal')
                $dataunit = StockUnit::where('stock_id', $stockid)->where('unit', $unit)->first();
            else if ($modeStock == "custom") {
                $dataunit = StockUnit::where('stock_id', $stockid)->where('unit', 'Pcs')->first();
            }

            if (!$dataunit) {
                if ($useTransaction)
                    DB::rollBack();
                return [
                    'status' => 0,
                    'msg' => 'unit tidak ditemukan'
                ];
            }
            $qtybackend = $qty * $dataunit->konversi;
            $unitbackend = $stock->unit_backend;
            if (!$productionNumber) {
                $productionNumber = $SONumber;
            }
            if (!$customStockName) {
                $customStockName = $stock->name;
            }
            $mutasiRupiahUnit = money($mutasiRupiahTotal / $qtybackend);
            $st = self::create(new Request([
                'stock_id' => $stockid,
                'sales_order_number' => $SONumber,
                'sales_order_id' => $SOID,
                'invoice_pack_number' => $invoiceNumber,
                'invoice_pack_id' => $invID,
                'mutasi_qty_backend' => $qtybackend,
                'unit_backend' => $unitbackend,
                'mutasi_quantity' => $qty,
                'unit' => $unit,
                'flow' => $flow,
                'is_custom_rupiah' => $isCustom,
                'mutasi_rupiah_on_unit' => $mutasiRupiahUnit,
                'mutasi_rupiah_total' => $mutasiRupiahTotal,
                'code_group' => $codeGroup,
                'date' => $date,
                'code_group_name' => $codeGroupName,
                'production_number' => $productionNumber,
                'custom_stock_name' => $customStockName,
            ]));
            if ($st['status'] == 0) {
                throw new \Exception($st['msg']);
            }

            $ks = $st['msg'];
            $number = null;
            if ($isOtomatisJurnal) {
                //buat kartu lawan yaa
                $amount = abs($ks->mutasi_rupiah_total);
                if ($flow == 1) {
                    //keluar
                    $codeDebet = $lawanCodeGroup;
                    $codeKredit = $codeGroup;
                    if (!$desc)
                        $desc =  $productionNumber . ' selesai';
                } else {
                    $codeDebet = $codeGroup;
                    $codeKredit = $lawanCodeGroup;
                    if (!$desc)
                        $desc = $productionNumber . ' sedang diproses';
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

                ]), false, $lockManager);
                if ($st['status'] != 1) throw new \Exception($st['msg']);
                $number = $st['journal_number'];
                $journal = Journal::where('journal_number', $number)->where('code_group', 140003)->first();
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
            'msg' => $st['msg'],
            'journal_number' => $number
        ];
    }

    public function recalculateSaldo()
    {
        $kartus = KartuBDP::where('stock_id', $this->stock_id)->where('index_date', '>', $this->index_date)->where('production_number', $this->production_number)
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
}
