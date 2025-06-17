<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use App\Models\KartuBahanJadi;
use App\Models\KartuBDP;
use App\Models\KartuStock;
use App\Models\SalesOrder;
use App\Models\Stock;
use App\Services\LockManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class KartuBDPController extends Controller
{
    //
    public function index()
    {
        $view = view('kartu.kartu-bdp');
        $view->month = getInput('month') ? toDigit(getInput('month'), 2) : date('m');
        $view->year = getInput('year') ? getInput('year') : date('Y');
        return $view;
    }

    public function getSummary()
    {
        $month = getInput('month') ?? date('m');
        $year = getInput('year') ?? date('Y');
       return KartuBDP::getSummaryProduction($year, $month);
    }

    public function createMutasiMasuk()
    {
        $view = view('kartu.modal._kartu-bdp-masuk');
        return $view;
    }
    public function createMutasiKeluar()
    {
        $view = view('kartu.modal._kartu-bdp-keluar');
        return $view;
    }

    public function createMutations(Request $request)
    {
        $stockIDs = $request->input('stock_id');
        $quantitys = $request->input('quantity');
        $spkNumbers = $request->input('spk_number');
        $productionNumber = $request->input('production_number');
        $units = $request->input('unit');
        $flows = $request->input('flow'); //harusnya ini 1 atau 0
        $codeGroup = 140003;

        $date = $request->input('date');
        $saleOrderId = $request->input('sales_order_id');
        $salesOrderNumber = $request->input('sales_order_number');
        $lawanCodeGroups = $request->input('lawan_code_group');
        $salesOrder = SalesOrder::where('sales_order_number', $salesOrderNumber)->first();
        $customer = $salesOrder->customer ? $salesOrder->customer->name : "";


        $allSt = [];
        try {
            DB::beginTransaction();
            $lockManager = new LockManager();
            foreach ($stockIDs as $row => $stock_id) {
                $qty = format_db($quantitys[$row]);
                $unit = $units[$row];
                $flow = $flows[$row];
                $lawanCodeGroup = $lawanCodeGroups[$row];
                if ($lawanCodeGroup == $codeGroup) {
                    throw new \Exception('Lawan code group tidak boleh sama dengan code group');
                }
                if ($flow == 0)
                    $desc = 'produksi ' . $customer . ' - ' . $productionNumber . ' dalam proses';
                else {
                    $desc = 'produksi ' . $customer . ' - ' . $productionNumber . ' selesai';
                }
                $isCustomRupiah = 0;
                $mutasiRupiahTotal = 0;
                if ($flow == 0) {
                    $typekartulawan = "stock";
                    if ($lawanCodeGroup == 140004) {
                        $typekartulawan = "barang jadi";
                    }
                    $hpp = Stock::find($stock_id)->getLastHPP($unit, $typekartulawan, $spkNumbers[$row]);

                    $mutasiRupiahTotal = $hpp * $qty;
                    $isCustomRupiah = 1;
                }
                $stStock = null;
                if ($lawanCodeGroup == 140001 || $lawanCodeGroup == 140002) {
                    //kalo bahan baku atau barang dagang
                    $stStock = KartuStock::mutationStore(new Request([
                        'stock_id' => $stock_id,
                        'mutasi_qty_backend' => $qty,
                        'unit_backend' => $unit,
                        'mutasi_quantity' => $qty,
                        'unit' => $unit,
                        'flow' => $flow == 1 ? 0 : 1,
                        'sales_order_number' =>  $salesOrderNumber,
                        'production_number' => $spkNumbers[$row],
                        'sales_order_id' => $saleOrderId,
                        'code_group' => $lawanCodeGroup,
                        'lawan_code_group' => $codeGroup,
                        'is_otomatis_jurnal' => 0,
                        'is_custom_rupiah' => $isCustomRupiah,
                        'mutasi_rupiah_total' => $mutasiRupiahTotal,
                        'date' => $date,
                        'description' => $desc,


                    ]), false);
                    if ($stStock['status'] == 0) {
                        throw new \Exception($stStock['msg']);
                    }
                } else if ($lawanCodeGroup == 140004) {
                    //kalo barang jadi
                    $stStock = KartuBahanJadi::mutationStore(new Request([
                        'stock_id' => $stock_id,
                        'mutasi_qty_backend' => $qty,
                        'unit_backend' => $unit,
                        'mutasi_quantity' => $qty,
                        'unit' => $unit,
                        'flow' => $flow == 1 ? 0 : 1,
                        'sales_order_number' => $salesOrderNumber,
                        'production_number' => $spkNumbers[$row],
                        'sales_order_id' => $saleOrderId,
                        'code_group' => $lawanCodeGroup,
                        'lawan_code_group' => $codeGroup,
                        'is_otomatis_jurnal' => 0,
                        'is_custom_rupiah' => $isCustomRupiah,
                        'mutasi_rupiah_total' => $mutasiRupiahTotal,
                        'date' => $date,
                        'description' => $desc
                    ]), false, $lockManager);
                    if ($stStock['status'] == 0) {
                        throw new \Exception($stStock['msg']);
                    }
                }
                $st = KartuBDP::mutationStore(new Request([
                    'stock_id' => $stock_id,
                    'mutasi_qty_backend' => $qty,
                    'unit_backend' => $unit,
                    'mutasi_quantity' => $qty,
                    'unit' => $unit,
                    'flow' => $flow,
                    'production_number' => $productionNumber,
                    'sales_order_number' => $salesOrderNumber,
                    'sales_order_id' => $saleOrderId,
                    'code_group' => $codeGroup,
                    'lawan_code_group' => $lawanCodeGroup,
                    'is_otomatis_jurnal' => 1,
                    'is_custom_rupiah' => $isCustomRupiah,
                    'mutasi_rupiah_total' => $mutasiRupiahTotal,
                    'date' => $date,
                    'description' => $desc
                ]), false, $lockManager);
                $allSt[] = $st;
                if ($st['status'] == 0) {
                    throw new \Exception($st['msg']);
                }
                if ($stStock) {
                    $kartuStock = $stStock['msg'];
                    $thejournal = Journal::where('journal_number', $st['journal_number'])->where('code_group', $lawanCodeGroup)->first();
                    $kartuStock->journal_id = $thejournal->id;
                    $kartuStock->journal_number = $st['journal_number'];
                    $kartuStock->save();
                    $kartuStock->createDetailKartuInvoice();
                }
            }
        } catch (Throwable $th) {
            $lockManager->releaseAll();
            DB::rollBack();
            return [
                'status' => 0,
                'msg' => $th->getMessage()
            ];
        }
        $lockManager->releaseAll();
        DB::commit();
        return [
            'status' => 1,
            'msg' => $allSt
        ];
    }
    public function getMutasiMasuk()
    {

        $month = getInput('month') ?? date('m');
        $year = getInput('year') ?? date('Y');
        $dateAwal = $year . '-' . $month . '-01 00:00:00';
        $dateAkhir = $year . '-' . $month . '-' . dayInMonthQuantity($month, $year) . ' 23:59:59';
        $kartu = KartuBDP::join('stocks', 'stocks.id', '=', 'kartu_bdps.stock_id')
            ->whereBetween('kartu_bdps.created_at', [$dateAwal, $dateAkhir])
            ->where('mutasi_qty_backend', '>', 0)
            ->select('kartu_bdps.*', 'stocks.name as stock_name')
            ->orderBy('index_date','asc')
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
        $kartu = KartuBDP::join('stocks', 'stocks.id', '=', 'kartu_bdps.stock_id')
            ->whereBetween('kartu_bdps.created_at', [$dateAwal, $dateAkhir])
            ->where('mutasi_qty_backend', '<', 0)
            ->select('kartu_bdps.*', 'stocks.name as stock_name')
            ->orderBy('index_date','asc')
            ->get();
        return [
            'status' => 1,
            'msg' => $kartu
        ];
    }

    public function mutasiStore(Request $request)
    {

        // return ['status' => 0, 'msg' => $request->all()];
        return KartuBDP::mutationStore($request,true);
    }
    public function refreshKartu(Request $request)
    {
        $id = $request->input('id');
        $kartu = KartuBDP::find($id);
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
}
