<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use App\Models\KartuBahanJadi;
use App\Models\KartuBDP;
use App\Models\KartuStock;
use App\Models\SalesOrder;
use App\Models\SalesOrderDetail;
use App\Models\Stock;
use App\Services\LockManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class KartuBahanJadiController extends Controller
{
    //

    public function index()
    {
        $view = view('kartu.kartu-bahan-jadi');
        $view->month = getInput('month') ? toDigit(getInput('month'), 2) : date('m');
        $view->year = getInput('year') ? getInput('year') : date('Y');
        return $view;
    }

    public function getSummary()
    {
        $month = getInput('month') ?? date('m');
        $year = getInput('year') ?? date('Y');
        return KartuBahanJadi::getSummaryProduction($year, $month);
    }


    public function createMutasiMasuk()
    {
        $view = view('kartu.modal._kartu-bahan-jadi-masuk');
        return $view;
    }
    public function createMutasiKeluar()
    {
        $view = view('kartu.modal._kartu-bahan-jadi-keluar');
        return $view;
    }

    public function createMutations(Request $request)
    {

        
        $stockIDs = $request->input('stock_id');
        $quantitys = $request->input('quantity');
        $units = $request->input('unit');
        $konversiJadi = $request->input('konversi_jadi');
        $flows = $request->input('flow'); //harusnya ini 1 atau 0
        $spkNumbers = $request->input('spk_number');
        $codeGroup = 140004;
        $date = $request->input('date') ?? now();
        $productionNumber = $request->input('production_number');
        $salesDetailIDs = $request->input('sales_detail_id');
        $saleOrderId = $request->input('sales_order_id');
        $salesOrderNumber = $request->input('sales_order_number');
        $lawanCodeGroups = $request->input('lawan_code_group');
        $customStockNames = $request->input('custom_stock_name');
        $allSt = [];
        $salesOrder = SalesOrder::where('sales_order_number', $salesOrderNumber)->first();
        $customer = $salesOrder->customer ? $salesOrder->customer->name : "";

        try {
            DB::beginTransaction();
            $lockManager = new LockManager();
            foreach ($salesDetailIDs as $row => $saleDetailID) {
                $qty = $quantitys[$row];
                $konversiJadi = $konversiJadi[$row] ?? 1;
                $unit = $units[$row];
                $flow = $flows[$row];
                if ($flow == 0)
                    $desc = 'produksi ' . $customer . ' - ' . $productionNumber . ' selesai';
                else {
                    $desc = 'produksi ' . $customer . ' dibebankan';
                }

                $lawanCodeGroup = $lawanCodeGroups[$row];
                // if ($lawanCodeGroup == $codeGroup) {
                //     throw new \Exception('Lawan code group tidak boleh sama dengan code group');
                // }
                $stock_id = $stockIDs[$row];
                $isCustomRupiah = 0;
                $mutasiRupiahTotal = 0;


                $stStock = null;
                $allStStock = [];
                if ($lawanCodeGroup == 140001 || $lawanCodeGroup == 140002) {
                    //kalo bahan baku atau barang dagang
                    if ($flow == 0) {
                        $typeKartuLawan = "stock";
                        $hpp = Stock::find($stock_id)->getLastHPP($unit, $typeKartuLawan, $spkNumbers[$row],$date);
                        $mutasiRupiahTotal = $hpp * $qty;
                        $isCustomRupiah = 1;
                    }
                    $stStock = KartuStock::mutationStore(new Request([
                        'stock_id' => $stock_id,
                        'mutasi_quantity' => $qty / $konversiJadi,
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
                        'description' => $desc
                    ]), false, $lockManager);
                    if ($stStock['status'] == 0) {
                        throw new \Exception($stStock['msg']);
                    }
                    $allStStock[] = $stStock['msg'];
                } else if ($lawanCodeGroup == 140003) {
                    //kalo dari barang dalam proses
                    //nah disini lo lu memungkinkan ada lebih dari satu kartu bdp

                    $stockIDCustom = KartuBDP::where('production_number', $spkNumbers[$row])->pluck('stock_id')->all();

                    //kalo bukan custom, berarti harus ada kartu bdp
                    // $lastCard = KartuBDP::where('stock_id', $stock_id)->where('production_number', $spkNumbers[$row])->orderBy('id', 'desc')->first();
                    if (count($stockIDCustom) == 0) {
                        throw new \Exception('tidak ada saldo stock pada nomer produksi ' . $spkNumbers[$row] . ', dan tidak ada pembebanan lain ');
                    }
                    // if ($lastCard) {
                    //     $prosenQty = ($qty / $konversiJadi) / ($lastCard->saldo_qty_backend * $lastCard->mutasi_quantity / $lastCard->mutasi_qty_backend);

                    //     $stStock = KartuBDP::mutationStore(new Request([
                    //         'stock_id' => $stock_id,
                    //         'mutasi_quantity' => $qty / $konversiJadi,
                    //         'unit' => $unit,
                    //         'flow' => $flow == 1 ? 0 : 1,
                    //         'sales_order_number' => $salesOrderNumber,
                    //         'production_number' => $spkNumbers[$row],
                    //         'sales_order_id' => $saleOrderId,
                    //         'code_group' => $lawanCodeGroup,
                    //         'lawan_code_group' => $codeGroup,
                    //         'is_otomatis_jurnal' => 0,
                    //         'is_custom_rupiah' => $isCustomRupiah,
                    //         'mutasi_rupiah_total' => $mutasiRupiahTotal,
                    //         'date' => $date,
                    //         'description' => $desc
                    //     ]), false, $lockManager);
                    //     if ($stStock['status'] == 0) {
                    //         throw new \Exception($stStock['msg']);
                    //     }
                    //     $allStStock[] = $stStock['msg'];
                    // } else {
                    $saleDetail = SalesOrderDetail::find($saleDetailID);
                    $prosenQty = $qty / $saleDetail->qtyjadi;
                    info(' prosenQty custom: ' . $prosenQty);
                    // }
                    foreach ($stockIDCustom as $customID) {
                        $lastCustomCard = KartuBDP::where('production_number', $spkNumbers[$row])
                            ->where('stock_id', $customID)->orderBy('id', 'desc')->first();
                        $qtyCustom = ($lastCustomCard->saldo_qty_backend * $lastCustomCard->mutasi_quantity / $lastCustomCard->mutasi_qty_backend)  * $prosenQty; //ini jadikan unit normal aja
                        info('name: ' . $lastCustomCard->custom_stock_name);
                        $rupiahCustom = $lastCustomCard->saldo_rupiah_total * $prosenQty;
                        $unitCustom = $lastCustomCard->unit;
                        info('qtycustom:' . $qtyCustom . ' ' . $unitCustom . ' - rupiahcustom:' . $rupiahCustom);
                        $stStock = KartuBDP::mutationStore(new Request([
                            'stock_id' => $customID,
                            'mutasi_quantity' => $qtyCustom,
                            'unit' => $unitCustom,
                            'flow' => $flow == 1 ? 0 : 1,
                            'sales_order_number' => $salesOrderNumber,
                            'production_number' => $spkNumbers[$row],
                            'sales_order_id' => $saleOrderId,
                            'code_group' => $lawanCodeGroup,
                            'lawan_code_group' => $codeGroup,
                            'is_otomatis_jurnal' => 0,
                            'is_custom_rupiah' => $isCustomRupiah,
                            'mutasi_rupiah_total' => $rupiahCustom,
                            'date' => $date,
                            'description' => $desc
                        ]), false, $lockManager);
                        if ($stStock['status'] == 0) {
                            throw new \Exception($stStock['msg']);
                        }
                        $allStStock[] = $stStock['msg'];
                    }
                } else if ($lawanCodeGroup == 140004) {
                    //kalo dari bahan jadi sendiri , cuma pindah kartu

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
                    $allStStock[] = $stStock['msg'];
                }
                $mutasiRupiahTotal = abs(collect($allStStock)->sum('mutasi_rupiah_total'));
                $st = KartuBahanJadi::mutationStore(new Request([
                    'stock_id' => $stock_id,
                    'mutasi_quantity' => $qty,
                    'unit' => $unit,
                    'flow' => $flow,
                    'sales_order_number' => $salesOrderNumber,
                    'production_number' => $productionNumber,
                    'sales_order_id' => $saleOrderId,
                    'code_group' => $codeGroup,
                    'custom_stock_name' => $customStockNames[$row],
                    'lawan_code_group' => $lawanCodeGroup,
                    'is_otomatis_jurnal' => 1,
                    'is_custom_rupiah' => $isCustomRupiah,
                    'mutasi_rupiah_total' => $mutasiRupiahTotal,
                    'date' => $date,
                    'description' => $desc,
                ]), false, $lockManager);


                if ($st['status'] == 0) {
                    throw new \Exception($st['msg']);
                }
                $allSt[] = $st['msg'];
                info(json_encode($st));
                foreach ($allStStock as $kartuStock) {

                    $thejournal = Journal::where('journal_number', $st['journal_number'])->where('code_group', $lawanCodeGroup)->first();
                    $kartuStock->journal_id = $thejournal->id;
                    $kartuStock->journal_number = $st['journal_number'];
                    $kartuStock->save();
                    $kartuStock->createDetailKartuInvoice();
                }
            }
        } catch (Throwable $th) {

            DB::rollBack();
            $lockManager->releaseAll();
            return [
                'status' => 0,
                'msg' => $th->getMessage()
            ];
        }
        DB::commit();
        $lockManager->releaseAll();

        return [
            'status' => 1,
            'msg' => $allSt,
            'kartubahan' => $allStStock
        ];
    }
    public function getMutasiMasuk()
    {

        $month = getInput('month') ?? date('m');
        $year = getInput('year') ?? date('Y');
        $dateAwal = $year . '-' . $month . '-01 00:00:00';
        $dateAkhir = $year . '-' . $month . '-' . dayInMonthQuantity($month, $year) . ' 23:59:59';
        $kartu = KartuBahanJadi::join('stocks', 'stocks.id', '=', 'kartu_bahan_jadis.stock_id')
            ->whereBetween('kartu_bahan_jadis.created_at', [$dateAwal, $dateAkhir])
            ->where('mutasi_qty_backend', '>', 0)
            ->select('kartu_bahan_jadis.*', 'stocks.name as stock_name')
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
        $kartu = KartuBahanJadi::join('stocks', 'stocks.id', '=', 'kartu_bahan_jadis.stock_id')
            ->whereBetween('kartu_bahan_jadis.created_at', [$dateAwal, $dateAkhir])
            ->where('mutasi_qty_backend', '<', 0)
            ->select('kartu_bahan_jadis.*', 'stocks.name as stock_name')
            ->orderBy('index_date', 'asc')
            ->get();
        return [
            'status' => 1,
            'msg' => $kartu
        ];
    }

    public function mutasiStore(Request $request)
    {
        return KartuBahanJadi::mutationStore($request);
    }
}
