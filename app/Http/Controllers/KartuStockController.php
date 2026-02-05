<?php

namespace App\Http\Controllers;

use App\Models\DetailKartuInvoice;
use App\Models\Journal;
use App\Models\KartuStock;
use App\Models\ManufStock;
use App\Models\RetailStock;
use App\Models\Stock;
use App\Models\StockCategory;
use App\Models\StockUnit;
use App\Models\TaskImportDetail;
use App\Services\ContextService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KartuStockController extends Controller
{

    public function index()
    {
        $view = view('kartu.kartu-stock');
        $view->month = getInput('month') ? toDigit(getInput('month'), 2) : date('m');
        $view->year = getInput('year') ? getInput('year') : date('Y');
        return $view;
    }

    public static function getSummary($month = null, $year = null)
    {
        if (!$month)
            $month = getInput('month') ?? date('m');
        if (!$year)
            $year = getInput('year') ?? date('Y');
        $dateAwal = $year . '-' . $month . '-01 00:00:00';
        $dateAkhir = $year . '-' . $month . '-' . dayInMonthQuantity($month, $year) . ' 23:59:59';
        $indexDateAwal = createCarbon($dateAwal)->format('ymdHis000');
        $indexDateAkhir = createCarbon($dateAkhir)->format('ymdHis999');
        $saldoAwal = kartuStock::whereIn('index_date', function ($q) use ($indexDateAwal) {
            $q->from('kartu_stocks')
                ->select(DB::raw('max(index_date)'))
                ->where('book_journal_id', bookID())
                ->where('index_date', '<', $indexDateAwal)
                ->groupBy('stock_id');
        });
        $saldoAkhir = kartuStock::whereIn('index_date', function ($q) use ($indexDateAkhir) {
            $q->from('kartu_stocks')
                ->select(DB::raw('max(index_date)'))
                ->where('book_journal_id', bookID())
                ->where('index_date', '<', $indexDateAkhir)
                ->groupBy('stock_id');
        });


        $arrayStock = $arrayStock = collect(
            (clone $saldoAwal)->pluck('stock_id')
        )->merge(
            (clone $saldoAkhir)->pluck('stock_id')
        )->unique()->all();

        $stock = Stock::whereIn('stocks.id', $arrayStock)->leftJoinSub($saldoAwal, 'saldoAwal', function ($join) {
            $join->on('stocks.id', '=', 'saldoAwal.stock_id');
        })->leftJoinSub($saldoAkhir, 'saldoAkhir', function ($join) {
            $join->on('stocks.id', '=', 'saldoAkhir.stock_id');
        })
            ->join('stock_categories', 'stocks.category_id', '=', 'stock_categories.id')
            ->join('stock_units', function ($join) {
                $join->on('stocks.id', '=', 'stock_units.stock_id')
                    ->on('stocks.unit_default', '=', 'stock_units.unit');
            })->select(
                'stocks.*',
                'stock_units.konversi as konversi',
                'stock_categories.name as category_name',
                DB::raw('coalesce(saldoAwal.saldo_qty_backend,0) as awal_qty'),
                DB::raw('coalesce(saldoAwal.saldo_rupiah_total,0) as awal_rupiah'),
                DB::raw('coalesce(saldoAkhir.saldo_qty_backend,0) as akhir_qty'),
                DB::raw('coalesce(saldoAkhir.saldo_rupiah_total,0) as akhir_rupiah'),
            )->get();



        $mutasiMasuk = KartuStock::where('index_date', '>=', $indexDateAwal)->where('index_date', '<=', $indexDateAkhir)
            ->where('mutasi_qty_backend', '>', 0)
            ->select(
                DB::raw('sum(coalesce(mutasi_qty_backend,0)) as qty'),

                DB::raw('sum(coalesce(mutasi_rupiah_total,0)) as total'),
                DB::raw('max(stock_id) as stock_id')

            )->groupBy('stock_id')
            ->get()->keyBy('stock_id');
        $mutasiKeluar = KartuStock::where('index_date', '>=', $indexDateAwal)->where('index_date', '<=', $indexDateAkhir)
            ->where('mutasi_qty_backend', '<', 0)
            ->select(
                DB::raw('sum(mutasi_qty_backend) as qty'),

                DB::raw('sum(mutasi_rupiah_total) as total'),
                DB::raw('max(stock_id) as stock_id')

            )->groupBy('stock_id')
            ->get()->keyBy('stock_id');
        return [
            'status' => 1,
            'msg' => $stock,
            'mutasi_masuk' => $mutasiMasuk,
            'mutasi_keluar' => $mutasiKeluar,
            'month' => $month,
            'year' => $year
        ];
    }

    public function getHPP()
    {
        $date = getInput('date') ?? date('Y-m-d 23:59:59');
        $indexDate = createCarbon($date)->format('ymdHis999');
        $stockid = getInput('stock_id');
        $unit = getInput('unit');
        if (!$date || !$stockid || !$unit) {
            return ['status' => 0, 'msg' => 'Tanggal, Stock ID dan Unit harus diisi'];
        }
        $kartu = KartuStock::where('kartu_stocks.stock_id', $stockid)
            ->join('stock_units as su', 'su.stock_id', '=', 'kartu_stocks.stock_id')
            ->where('su.unit', $unit)
            ->where('kartu_stocks.index_date', '<=', $indexDate)
            ->select(


                DB::raw('coalesce(kartu_stocks.saldo_rupiah_total/ kartu_stocks.saldo_qty_backend,0) as hppbackend'),
                DB::raw('su.konversi')
            )
            ->orderBy('kartu_stocks.index_date', 'desc')
            ->first();
        return ['status' => 1, 'msg' => $kartu];
    }

    public function destroy($id)
    {
        $kartu = KartuStock::find($id);

        if (!$kartu) {
            return ['status' => 0, 'msg' => 'Kartu tidak ditemukan'];
        }
        if ($kartu->journal_id) {
            return ['status' => 0, 'msg' => 'Kartu ini sudah terhubung dengan jurnal'];
        }

        $dk = DetailKartuInvoice::where('kartu_type', KartuStock::class)
            ->where('kartu_id', $kartu->id)->get();
        foreach ($dk as $d) {
            $d->delete();
        }
        $kartu->delete();
        return ['status' => 1, 'msg' => 'Kartu berhasil dihapus'];
    }
    public function createMutasiMasuk()
    {
        $view = view('kartu.modal._kartu-stock-masuk');
        return $view;
    }
    public function createMutasiKeluar()
    {
        $view = view('kartu.modal._kartu-stock-keluar');
        return $view;
    }
    public function getMutasiMasuk()
    {

        $month = getInput('month') ?? date('m');
        $year = getInput('year') ?? date('Y');
        $dateAwal = $year . '-' . $month . '-01 00:00:00';
        $dateAkhir = $year . '-' . $month . '-' . dayInMonthQuantity($month, $year) . ' 23:59:59';
        $kartu = KartuStock::join('stocks', 'stocks.id', '=', 'kartu_stocks.stock_id')
            ->whereBetween('kartu_stocks.created_at', [$dateAwal, $dateAkhir])
            ->where('mutasi_qty_backend', '>', 0)
            ->select('kartu_stocks.*', 'stocks.name as stock_name')
            ->get();
        return [
            'status' => 1,
            'msg' => $kartu
        ];
    }
    public function getMutasiKeluar()
    {
        $month = getInput('month') ?? date('m');
        $year = getInput('year') ?? date('Y');
        $dateAwal = $year . '-' . $month . '-01 00:00:00';
        $dateAkhir = $year . '-' . $month . '-' . dayInMonthQuantity($month, $year) . ' 23:59:59';
        $kartu = KartuStock::join('stocks', 'stocks.id', '=', 'kartu_stocks.stock_id')
            ->whereBetween('kartu_stocks.created_at', [$dateAwal, $dateAkhir])
            ->where('mutasi_qty_backend', '<', 0)
            ->select('kartu_stocks.*', 'stocks.name as stock_name')
            ->get();
        return [
            'status' => 1,
            'msg' => $kartu
        ];
    }

    public function mutasiStore(Request $request)
    {

        return KartuStock::mutationStore($request);
    }

    public function refreshKartu(Request $request)
    {
        $id = $request->input('id');
        $kartu = KartuStock::find($id);
        if ($kartu->journal_id && !$kartu->journal_number) {
            $journal = Journal::find($kartu->journal_id);
            $kartu->journal_number = $journal->journal_number;
        }

        $kartu->save();
        if (!$kartu->isHasKartuInvoice()) {
            $kartu->createDetailKartuInvoice();
        }
        return ['status' => 1, 'msg' => $kartu];
    }

    public function recalculate(Request $request)
    {
        $id = $request->input('id');
        try {
            $kartu = KartuStock::find($id);
            $kartu->recalculateSaldo();

            return ['status' => 1, 'msg' => $kartu];
        } catch (\Exception $e) {
            return ['status' => 0, 'msg' => $e->getMessage()];
        }
    }

    public function kartuMutasi($stockid)
    {
        $view = view('kartu.modal._kartu-mutasi-stock');
        $kartuStocks = KartuStock::from('kartu_stocks as ks')->where('ks.stock_id', $stockid)
            ->join('journals as j', 'j.id', '=', 'ks.journal_id')
            ->join('stocks as st', 'st.id', '=', 'ks.stock_id')
            ->join('stock_units as su', function ($join) {
                $join->on('ks.stock_id', '=', 'su.stock_id')
                    ->on('su.unit', '=', 'st.unit_default');
            })

            ->select(
                'ks.created_at',
                'ks.id as uid',
                'j.description',
                'j.journal_number',
                'st.unit_default as unit',
                DB::raw('(ks.mutasi_qty_backend/su.konversi) as mutasi'),
                DB::raw('(ks.saldo_qty_backend/su.konversi) as saldo'),
                DB::raw('(ks.saldo_rupiah_total) as saldo_rupiah')
            )->orderBy('ks.index_date', 'asc')->get();
        $stock = Stock::find($stockid);
        $view->data = $kartuStocks;
        $view->stock = $stock;
        return $view;
    }


    public function showHistoryStock($id)
    {
        $view = view('kartu.modal._kartu-history-stock');
        $year = Date('Y');
        $stock = Stock::find($id);
        $kartuStock = KartuStock::where('stock_id', $id)->whereYear('created_at', $year)
            ->select(
                DB::raw('count(*) as total'),
                'unit'
            )
            ->groupBy('unit')->orderBy(DB::raw('count(*)'), 'desc')->first();
        $unit = $kartuStock ? $kartuStock->unit : $stock->unit_default;


        $dataHistory = KartuStock::from('kartu_stocks as ks')
            ->leftJoin('stock_units as u', function ($join) use ($unit) {
                $join->on('u.unit', '=', DB::raw("'" . $unit . "'"))
                    ->on('u.stock_id', '=', 'ks.stock_id');
            })
            ->leftJoin('journals as j', 'j.id', '=', 'ks.journal_id')
            ->where('ks.stock_id', $id)
            ->select(
                'ks.id',
                'ks.created_at',
                'j.description',
                DB::raw('case when ks.mutasi_qty_backend > 0 then ks.mutasi_qty_backend/u.konversi else 0 end as qty_debet'),
                DB::raw('case when ks.mutasi_qty_backend < 0 then abs(ks.mutasi_qty_backend)/u.konversi else 0 end as qty_kredit'),
                DB::raw("'" . $unit . "' as unit"),
                DB::raw('case when mutasi_rupiah_total >0 then mutasi_rupiah_total else 0 end as rupiah_debet'),
                DB::raw('case when mutasi_rupiah_total <0 then abs(mutasi_rupiah_total) else 0 end as rupiah_kredit'),
                DB::raw('saldo_qty_backend/u.konversi as qty_saldo'),
                'saldo_rupiah_total as rupiah_saldo',
                'ks.journal_number',

            )->get();
        $view->title = $stock->name . ' [' . $stock->id . ']';
        $view->datas = $dataHistory;
        $view->model = 'kartu-stock';
        return $view;
    }

    public static function processTaskImport($taskID)
    {
        $task = TaskImportDetail::find($taskID);
        ContextService::setBookJournalID($task->book_journal_id);

        if ($task->status == 'success') {
            return;
        }
        $data = json_decode($task->payload, true);
        $qty = $data['quantity'];
        $unit = $data['unit'];
        info('data :' . json_encode($data));
        $lawanCode = 301000;
        $bookModel = $task->book_journal_id == 1 ? ManufStock::class : RetailStock::class;
        info('ref_id:' . intval($data['ref_id']));
        info('book model:' . $bookModel);
        $stock = Stock::where('reference_stock_id', intval($data['ref_id']))
            ->where('reference_stock_type', $bookModel)
            ->first();
        if (!$stock)
            $stock = Stock::where('name', $data['name'])->first();
      
        info('stock terdaftar:' . json_encode($stock));

        try {
            if (!$stock) {
                if ($task->book_journal_id == 1) {
                    throw (new \Exception('masuk ke buku jurnal manufaktur'));
                    $manufStock = ManufStock::where('name', $data['name'])->with(['parentCategory', 'category'])->first();
                    if (!$manufStock) {
                        $manufStock = ManufStock::where('id', intval($data['ref_id']))->with(['parentCategory', 'category'])->first();
                    }
                    if ($manufStock) {
                        $manufStock['units_manual'] = $manufStock->getUnits();
                        $manufStock['unit_default'] = $manufStock->unit_info;
                        $st = StockController::sync(new Request([
                            'book_journal_id' => 1,
                            'data' => $manufStock,
                            'stock_id' => $manufStock->id,
                        ]));
                        if ($st['status'] == 0) {
                            throw new \Exception($st['msg']);
                        }
                        $stock = $st['msg'];
                    }
                } else if ($task->book_journal_id == 2) {

                    $retailStock = RetailStock::where('name', $data['name'])->with(['parentCategory', 'category'])->first();

                    if (!$retailStock) {
                        $retailStock = RetailStock::where('id', intval($data['ref_id']))->with(['parentCategory', 'category'])->first();
                    }
                    if ($retailStock) {
                        $retailStock['units_manual'] = $retailStock->getUnits();
                        $retailStock['unit_default'] = $retailStock->unit_info;

                        $st = StockController::sync(new Request([
                            'book_journal_id' => 2,
                            'data' => $retailStock,
                            'stock_id' => $retailStock->id,
                        ]));
                        if ($st['status'] == 0) {
                            throw new \Exception($st['msg']);
                        }
                        $stock = $st['msg'];
                        throw (new \Exception('masuk ke buku jurnal retail'));
                    } else {
                        throw new \Exception('retail stock tidak ditemukan');
                    }
                }
                if (!$stock) {
                    //nah disini buat stock nih
                    $unitBackend = 'Pcs';
                    if ($unit == 'Kg' || $unit == 'Gram') {
                        $unitBackend = 'Gram';
                    }
                    if ($unit == 'Meter' || $unit == 'Cm' || $unit == 'm') {
                        $unitBackend = 'Meter';
                    }

                    $unknownCat = StockCategory::where('name', 'unknown')->first();
                    if (!$unknownCat) {
                        $unknownCat = StockCategory::create([
                            'name' => 'unknown',
                            'parent_id' => null
                        ]);
                    }
                    $stockNameFirst = explode(' ', $data['name'])[0];
                    $category = StockCategory::where('name', 'like', '%' . $stockNameFirst . '%')->first();
                    if (!$category) {
                        $category = $unknownCat;
                    }
                    $stock = Stock::create([
                        'name' => $data['name'],
                        'unit_backend' => $unitBackend,
                        'parent_category_id' => $category->parent_id,
                        'category_id' => $category->id,
                        'unit_default' => $data['unit'],
                        'book_journal_id' => $task->book_journal_id,
                    ]);
                    StockUnit::create([
                        'stock_id' => $stock->id,
                        'unit' => $data['unit'],
                        'konversi' => 1,
                    ]);
                }
            }
            if ($stock && ($stock['unit_default'] == null || $stock['units_manual'] == null)) {
                
                $referenceStock = $bookModel::find(intval($data['ref_id']));
                $referenceStock['units_manual'] = $referenceStock->getUnits();
                $referenceStock['unit_default'] = $referenceStock->unit_info;
                $st = StockController::sync(new Request([
                    'book_journal_id' => 2,
                    'data' => $referenceStock,
                    'stock_id' => $referenceStock->id,
                    'master_stock_id' => $stock->id,
                ]));
                if ($st['status'] == 0) {
                    throw new \Exception($st['msg']);
                }
                $stock = $st['msg'];
            }

            DB::beginTransaction();
            if ($task->processed_at == null)
                $task->processed_at = now();

            $journal = TaskImportDetail::where('task_import_id', $task->task_import_id)->whereNotNull('journal_number')->first();
            $journalNumber = $journal ? $journal->journal_number : null;
            if ($data['amount'] > 0) {
                // throw new \Exception('oke amount saldo  > 0');
                info('try to create kartu stock');
                $stStock = KartuStock::mutationStore(new Request([
                    'stock_id' => $stock->id,
                    'date' => createCarbon($data['date']),
                    'mutasi_quantity' => floatval($qty),
                    'unit' => $unit,
                    'flow' => 0,
                    'sales_order_number' => 'INITAWAL',
                    'code_group' => 140001,
                    'lawan_code_group' => $lawanCode,
                    'is_otomatis_jurnal' => false,
                    'journal_number' => $journalNumber,
                    'is_custom_rupiah' => 1,
                    'mutasi_rupiah_total' => floatval($data['amount']),

                ]), false);
                info('hasil dari kartu stcok:' . json_encode($stStock));
                if ($stStock['status'] == 0) {
                    throw new \Exception($stStock['msg']);
                }
            }

            $task->status = 'success';
            $task->error_message = "";
            $task->journal_number = $journalNumber;
            $task->finished_at = now();
            $task->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            if ($task->processed_at == null)
                $task->processed_at = now();
            $task->error_message = $e->getMessage();
            $task->status = 'failed';
            $task->save();
            return [
                'status' => 0,
                'msg' => 'Processing task import kartu stock failed: ' . $e->getMessage(),
                'task' => $task
            ];
        }

        return [
            'status' => 1,
            'msg' => 'Processing task import kartu stock completed.',
            'task' => $task
        ];
    }
}
