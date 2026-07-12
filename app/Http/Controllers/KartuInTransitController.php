<?php

namespace App\Http\Controllers;

use App\Models\ChartAccountAlias;
use App\Models\InvoicePack;
use App\Models\Journal;
use App\Models\KartuBahanJadi;
use App\Models\KartuBDP;
use App\Models\KartuInTransit;

use App\Models\KartuStock;
use App\Models\ManufStock;
use App\Models\RetailStock;
use App\Models\SalesOrder;
use App\Models\Stock;
use App\Models\StockCategory;
use App\Models\StockUnit;
use App\Models\TaskImportDetail;
use App\Services\ContextService;
use App\Services\LockManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class KartuInTransitController extends Controller
{
    //
    public function index()
    {
        $view = view('kartu.kartu-in-transit');
        $view->month = getInput('month') ? toDigit(getInput('month'), 2) : date('m');
        $view->year = getInput('year') ? getInput('year') : date('Y');
        return $view;
    }

    public function getSummary()
    {
        $month = getInput('month') ?? date('m');
        $year = getInput('year') ?? date('Y');
        return KartuInTransit::getSummaryProduction($year, $month);
    }

    public function createMutasiMasuk()
    {
        $view = view('kartu.modal._kartu-in-transit-masuk');
        return $view;
    }
    public function createMutasiKeluar()
    {
        $view = view('kartu.modal._kartu-in-transit-keluar');
        return $view;
    }

    public function pindahkan(Request $request)
    {

            try {
             DB::transaction( function() use($request){
                $lockManager = new LockManager();
                $date=$request->input('date');

              
                $kartuId= $request->input('kartu_id');
                $lastKartu= KartuInTransit::find($kartuId);
                if(!$lastKartu){
                    throw new \Exception('Kartu in transit tidak ditemukan');
                }
                $productionNumber = $lastKartu->production_number;
                if(!$productionNumber){
                    throw new \Exception('Production number tidak ditemukan');
                }
                $stock= Stock::find($lastKartu->stock_id);
                if(!$stock){
                    throw new \Exception('tidak ada kartu stock');
                }
                $lawanAkunKode= $request->input('account_code');
                $lawanAkun= ChartAccountAlias::where('code_group',$lawanAkunKode)->first();
                if(!$lawanAkun){
                    throw new \Exception('Akun lawan tidak ditemukan');
                }
                $akun= ChartAccountAlias::where('reference_model','App\Models\KartuInTransit')->first();
                if(!$akun){
                    throw new \Exception('Akun Kartu in transit tidak ditemukan');
                }
                $lawanModel= $lawanAkun->reference_model;
                $invoicePack= InvoicePack::where('invoice_number',$lastKartu->production_number)->first();
                if(!$invoicePack){
                    throw new \Exception('Invoice pack tidak ditemukan');
                }
               
                 $st = KartuInTransit::mutationStore(new Request([
                    'stock_id' => $lastKartu->stock_id,
                    'mutasi_qty_backend' => $lastKartu->saldo_qty_backend,
                    'unit_backend' => $lastKartu->unit_backend,
                    'mutasi_quantity' => $lastKartu->saldo_qty_backend*($lastKartu->mutasi_quantity/$lastKartu->mutasi_qty_backend),
                    'unit' => $lastKartu->unit,
                    'flow' => 1,// keluar
                    'production_number' => $productionNumber,
                    'sales_order_number' => null,
                    'sales_order_id' => null,
                    'invoice_pack_number' => $invoicePack->invoice_number,
                    'code_group' => $akun->code_group,
                    'lawan_code_group' => $lawanAkun->code_group,
                    'is_otomatis_jurnal' => 1,
                    'is_custom_rupiah' => 0,
                    'mutasi_rupiah_total' => $lastKartu->saldo_rupiah_total,
                    'date' => $date,
                    'description' => 'Pindahkan '.$stock->name.' ['.$productionNumber.'] ke '.$lawanAkun->name,
                ]), false, $lockManager);
                if($st['status']==0){
                    throw new \Exception($st['msg']);
                }
                $journalNumber= $st['journal_number'];
                 $stLawan = $lawanModel::mutationStore(new Request([
                        'stock_id' => $lastKartu->stock_id,
                        'mutasi_qty_backend' => $lastKartu->saldo_qty_backend,
                        'unit_backend' => $lastKartu->unit_backend,
                        'mutasi_quantity' => $lastKartu->saldo_qty_backend*($lastKartu->mutasi_quantity/$lastKartu->mutasi_qty_backend),
                        'unit' => $lastKartu->unit,
                        'flow' => 0,
                        'production_number' => $productionNumber,
                        'sales_order_number' => null,
                        'sales_order_id' => null,
                        'code_group' => $lawanAkun->code_group,
                        'lawan_code_group' => $akun->code_group,
                        'is_otomatis_jurnal' => 1,
                        'is_custom_rupiah' =>0,
                        'mutasi_rupiah_total' => $lastKartu->saldo_rupiah_total,
                        'date' => $date,
                        'description' => 'Pindahan '.$stock->name.' ['.$productionNumber.'] dari '.$akun->name,
                    ]), false, $lockManager);
                $resultLawan= $stLawan['msg'];
                $resultLawan->journal_number= $journalNumber;
                $resultLawan->save();
                $resultLawan->createDetailKartuInvoice();
             });

            
        } catch (Throwable $th) {         
     
            return [
                'status' => 0,
                'msg' => $th->getMessage()
            ];
        }
       
        return [
            'status' => 1,
            'msg' => 'success'
        ];
    }
    public function getMutasiMasuk()
    {

        $month = getInput('month') ?? date('m');
        $year = getInput('year') ?? date('Y');
        $dateAwal = $year . '-' . $month . '-01 00:00:00';
        $dateAkhir = $year . '-' . $month . '-' . dayInMonthQuantity($month, $year) . ' 23:59:59';
        $kartu = KartuInTransit::join('stocks', 'stocks.id', '=', 'kartu_in_transits.stock_id')
            ->whereBetween('kartu_in_transits.created_at', [$dateAwal, $dateAkhir])
            ->where('mutasi_qty_backend', '>', 0)
            ->select('kartu_in_transits.*', 'stocks.name as stock_name')
            ->orderBy('index_date', 'asc')
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
        $kartu = KartuInTransit::join('stocks', 'stocks.id', '=', 'kartu_in_transits.stock_id')
            ->whereBetween('kartu_in_transits.created_at', [$dateAwal, $dateAkhir])
            ->where('mutasi_qty_backend', '<', 0)
            ->select('kartu_in_transits.*', 'stocks.name as stock_name')
            ->orderBy('index_date', 'asc')
            ->get();
        return [
            'status' => 1,
            'msg' => $kartu
        ];
    }

    public function showHistoryStock($id)
    {
        $productionNumber = getInput('production_number');
        if (!$productionNumber) {
            throw new \Exception('Production number is required');
        }
        $view = view('kartu.modal._kartu-history-stock');
        $year = Date('Y');
        $stock = Stock::find($id);
        $kartuStock = KartuInTransit::where('stock_id', $id)->where('production_number', $productionNumber)->whereYear('created_at', $year)
            ->select(
                DB::raw('count(*) as total'),
                'unit',
                'custom_stock_name'
            )
            ->groupBy('unit')->orderBy(DB::raw('count(*)'), 'desc')->first();
        $unit = $kartuStock ? $kartuStock->unit : $stock->unit_default;
        $name = $kartuStock ? $kartuStock->custom_stock_name : $stock->name;
        $dataHistory = KartuInTransit::from('kartu_in_transits as ks')
            ->leftJoin('stock_units as u', function ($join) use ($unit) {
                $join->on('u.unit', '=', DB::raw("'" . $unit . "'"))
                    ->on('u.stock_id', '=', 'ks.stock_id');
            })
            ->leftJoin('journals as j', 'j.id', '=', 'ks.journal_id')
            ->where('ks.stock_id', $id)
            ->where('ks.production_number', $productionNumber)
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
        $accountPersediaan= ChartAccountAlias::whereBetween('code_group',[140000,150000])->select('code_group',DB::raw('concat(code_group," - ",name) as name'))->get();
        $view->productionNumber = $productionNumber;
        $view->title = $name . ' [' . $stock->id . ']';
        $view->datas = $dataHistory;
        $view->model = 'kartu-in-transit';
        $view->accountPersediaan = $accountPersediaan;
        return $view;
    }

    public function recalculate(Request $request)
    {
        $id = $request->input('id');
        try {
            $kartu = KartuInTransit::find($id);
            $kartu->recalculateSaldo();

            return ['status' => 1, 'msg' => $kartu];
        } catch (\Exception $e) {
            return ['status' => 0, 'msg' => $e->getMessage()];
        }
    }
    public function mutasiStore(Request $request)
    {

        // return ['status' => 0, 'msg' => $request->all()];
        return KartuInTransit::mutationStore($request, true);
    }
    public function refreshKartu(Request $request)
    {
        $id = $request->input('id');
        $kartu = KartuInTransit::find($id);
        if ($kartu->journal_id && !$kartu->journal_number) {
            $journal = Journal::find($kartu->journal_id);
            $kartu->journal_number = $journal->journal_number;
        }
        $kartu->save();
        $St = $kartu->createDetailKartuInvoice();
        if ($St['status'] == 0) {
            return ['status' => 0, 'msg' => $St['msg']];
        }

        return ['status' => 1, 'msg' => $kartu];
    }


    public function deleteMutation(Request $request)
    {
        $id = $request->input('id');
        $kartu = KartuInTransit::find($id);
        if (!$kartu) {
            return ['status' => 0, 'msg' => 'Mutasi tidak ditemukan'];
        }
        if ($kartu->journal_id) {
            return ['status' => 0, 'msg' => 'Mutasi sudah memiliki jurnal, hapus dari jurnalnya'];
        }
        $blokirJurnal = false;
        $details = $kartu->getDetailKartus();
        foreach ($details as $detail) {
            if ($detail->journal_id || $detail->journal_number) {
                $blokirJurnal = true;
                break;
            }
        }
        if ($blokirJurnal) {
            return ['status' => 0, 'msg' => 'Mutasi memiliki jurnal, hapus dari jurnalnya'];
        }
        return $kartu->makeDelete();
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
        $invoiceNumber = $data['invoice_pack_number'];
        info('data :' . json_encode($data));
        $lawanCode = 301000;
        $bookModel = $task->book_journal_id == 1 ? ManufStock::class : RetailStock::class;
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
            if (round(format_db($data['amount'])) > 0) {
                // throw new \Exception('oke amount saldo  > 0');
                info('try to create kartu stock transit');


                $st = KartuInTransit::mutationStore(new Request([
                    'stock_id' => $stock->id,
                    'mutasi_qty_backend' => $qty,
                    'unit_backend' => $unit,
                    'mutasi_quantity' => $qty,
                    'unit' => $unit,
                    'flow' => 0,
                    'invoice_pack_number' => $invoiceNumber,
                    'production_number' => $invoiceNumber,
                    'sales_order_number' => null,
                    'sales_order_id' => null,
                    'code_group' => 140002,
                    'lawan_code_group' => $lawanCode,
                    'is_otomatis_jurnal' => false,
                    'is_custom_rupiah' => 1,
                    'journal_number' => $journalNumber,
                    'mutasi_rupiah_total' => floatval($data['amount']),
                    'date' => $data['date'],
                    'description' => 'INIT AWAL - ' . $data['date'],
                    'tag' => 'init_import' . $data['date']
                ]), false);
                info('hasil dari kartu stock transit:' . json_encode($st));
                if ($st['status'] == 0) {
                    throw new \Exception($st['msg']);
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
