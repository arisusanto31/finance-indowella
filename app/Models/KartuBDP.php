<?php

namespace App\Models;

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
                    ->orWhere("{$alias}.book_journal_id", session('book_journal_id'));
            });
        });
    }


    public static function create(Request $request)
    {
        $lock = Cache::lock('kartu-stock-' . $request->input('stock_id'), 40);
        try {
            $flow = $request->input('flow');
            $isCustom = $request->input('is_custom_rupiah');
            $kartu = new KartuBDP;
            $kartu->stock_id = $request->input('stock_id');
            $kartu->mutasi_qty_backend = $request->input('mutasi_qty_backend');
            $kartu->unit_backend = $request->input('unit_backend');
            $kartu->mutasi_quantity = $request->input('mutasi_quantity');
            $kartu->unit = $request->input('unit');
            $kartu->spk_number = $request->input('spk_number');
            $kartu->sale_order_id = $request->input('sale_order_id');
            //mutasi terakhir sebelum mutasi id yg diinput oleh user
            $lastCard = KartuBDP::where('stock_id', $kartu->stock_id)->where('spk_number', $kartu->spk_number)->orderBy('id', 'desc')->first();

            if (!$lastCard) {
                $lastCard = new KartuBDP;
                $lastCard->saldo_qty_backend = 0;
                $lastCard->saldo_rupiah_total = 0;
            }

            if ($isCustom == 0) {
                //ini ngambil hpp yang lama.
                $rupiahUnit = $lastCard->saldo_rupiah_total / $lastCard->saldo_qty_backend;
                $kartu->mutasi_rupiah_on_unit = $rupiahUnit; //ini kayak hpp gitu. pake defaultnya
                $kartu->mutasi_rupiah_total = moneyMul($rupiahUnit, $kartu->mutasi_qty_backend);
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
            $kartu->book_journal_id = session('book_journal_id');
            $kartu->saldo_qty_backend = moneyAdd($lastCard->saldo_qty_backend, $kartu->mutasi_qty_backend);
            $kartu->saldo_rupiah_total = moneyAdd($lastCard->saldo_rupiah_total, $kartu->mutasi_rupiah_total);
            if ($kartu->saldo_rupiah_total < 0 || $kartu->saldo_qty_backend < 0) {
                return [
                    'status' => 0,
                    'msg' => 'invalid input, saldo minus jika diinput!'
                ];
            }
            $kartu->save();
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

    public static function mutationStore(Request $request, $useTransaction = true)
    {

        if ($useTransaction)
            DB::beginTransaction();
        try {
            $stockid = $request->input('stock_id');
            $qty = $request->input('mutasi_quantity');
            $unit = $request->input('unit');
            $flow = $request->input('flow');
            $codeGroup = $request->input('code_group');
            $chart = ChartAccount::where('code_group', $codeGroup)->first();

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

            $isCustom = $request->input('is_custom_rupiah');
            $dataunit = StockUnit::where('stock_id', $stockid)->where('unit', $unit)->first();

            $stock = Stock::find($stockid);
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
                'stock_id' => $stockid,
                'mutasi_qty_backend' => $qtybackend,
                'unit_backend' => $unitbackend,
                'mutasi_quantity' => $qty,
                'unit' => $unit,
                'flow' => $flow,
                'spk_number' => $request->input('spk_number'),
                'sale_order_id' => $request->input('sale_order_id'),
                'is_custom_rupiah' => $isCustom,
                'mutasi_rupiah_on_unit' => $mutasiRupiahUnit,
                'mutasi_rupiah_total' => $mutasiRupiahTotal,
                'code_group' => $codeGroup,
                'code_group_name' => $codeGroupName,
            ]));
            return $st;
            if ($st['status'] == 0) {
                new Throwable($st['msg']);
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
