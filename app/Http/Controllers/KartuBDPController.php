<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use App\Models\KartuBahanJadi;
use App\Models\KartuBDP;
use App\Models\KartuStock;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class KartuBDPController extends Controller
{
    //
    public function index()
    {
        return view('kartu.kartu-bdp');
    }

    public function getSummary()
    {
        $month = getInput('month') ?? date('m');
        $year = getInput('year') ?? date('Y');
        $dateAwal = $year . '-' . $month . '-01 00:00:00';
        $dateAkhir = $year . '-' . $month . '-' . dayInMonthQuantity($month, $year) . ' 23:59:59';

        $saldoAkhir = KartuBDP::whereIn('id', function ($q) use ($dateAkhir) {
            $q->from('kartu_bdps')
                ->select(DB::raw('max(id)'))
                ->where('book_journal_id', session('book_journal_id'))
                ->where('created_at', '<', $dateAkhir)
                ->groupBy('stock_id', 'production_number');
        })->select('production_number', 'stock_id', 'saldo_qty_backend as saldo_qty_awal', 'saldo_rupiah_total as saldo_rupiah_awal', DB::raw('"0" as saldo_qty_akhir'), DB::raw('"0" as saldo_rupiah_akhir'));

        $summary = KartuBDP::whereIn('kartu_bdps.id', function ($q) use ($dateAwal) {
            $q->from('kartu_bdps')
                ->select(DB::raw('max(id)'))
                ->where('book_journal_id', session('book_journal_id'))
                ->where('created_at', '<', $dateAwal)
                ->groupBy('stock_id', 'production_number');
        })->select('production_number', 'stock_id', 'saldo_qty_backend as saldo_qty_akhir', 'saldo_rupiah_total as saldo_rupiah_akhir', DB::raw('"0" as saldo_qty_awal'), DB::raw('"0" as saldo_rupiah_awal'))
            ->union($saldoAkhir)->get();

        $dataStock = Stock::whereIn('stocks.id', $summary->pluck('stock_id')->all())->join('stock_categories', 'stocks.category_id', '=', 'stock_categories.id')
            ->join('stock_units', function ($join) {
                $join->on('stocks.id', '=', 'stock_units.stock_id')
                    ->on('stocks.unit_default', '=', 'stock_units.unit');
            })->select(
                'stocks.*',
                'stock_units.konversi as konversi',
                'stock_categories.name as category_name',
            )->get()->keyBy('id')->all();

        $summary = $summary->groupBy('production_number')
            ->map(function ($dataspk, $productionNumber) use ($dataStock) {
                return collect($dataspk)->groupBy('stock_id')->map(function ($item, $stockid) use ($dataStock, $productionNumber) {
                    $data = []; //$dataStock[$stockid];
                    if (array_key_exists($stockid, $dataStock)) {
                        $data['name'] = $dataStock[$stockid]->name;
                        $data['konversi'] = $dataStock[$stockid]->konversi;
                        $data['category_name'] = $dataStock[$stockid]->category_name;

                        $data['unit_default'] = $dataStock[$stockid]->unit_default;
                    } else {
                        //ini pasti stock custom soalnya heuheu
                        $kartu = KartuBDP::where('stock_id', $stockid)->where('production_number', $productionNumber)->first();
                        $data['name'] = $kartu->custom_stock_name;
                        $data['konversi'] = 1;
                        $data['category_name'] = 'custom';
                        $data['unit_default'] = $kartu->unit;
                    }
                    $data['id'] = $stockid;
                    $data['saldo_qty_awal'] = collect($item)->sum('saldo_qty_awal');
                    $data['saldo_rupiah_awal'] = collect($item)->sum('saldo_rupiah_awal');
                    $data['saldo_qty_akhir'] = collect($item)->sum('saldo_qty_akhir');
                    $data['saldo_rupiah_akhir'] = collect($item)->sum('saldo_rupiah_akhir');

                    return $data;
                })->values();
            });




        $mutasiMasuk = KartuBDP::whereBetween('created_at', [$dateAwal, $dateAkhir])
            ->where('mutasi_qty_backend', '>', 0)
            ->select(
                DB::raw('sum(coalesce(mutasi_qty_backend,0)) as qty'),
                DB::raw('sum(coalesce(mutasi_rupiah_on_unit,0)) as rupiah_unit'),
                DB::raw('sum(coalesce(mutasi_rupiah_total,0)) as total'),
                DB::raw('max(stock_id) as stock_id'),
                'production_number'

            )->groupBy('stock_id', 'production_number')
            ->get()->groupBy('production_number')->map(function ($val) {
                return $val->keyBy('stock_id');
            });
        $mutasiKeluar = KartuBDP::whereBetween('created_at', [$dateAwal, $dateAkhir])
            ->where('mutasi_qty_backend', '<', 0)
            ->select(
                DB::raw('sum(mutasi_qty_backend) as qty'),
                DB::raw('sum(mutasi_rupiah_on_unit) as rupiah_unit'),
                DB::raw('sum(mutasi_rupiah_total) as total'),
                DB::raw('max(stock_id) as stock_id'),
                'production_number'

            )->groupBy('stock_id', 'production_number')
            ->get()->groupBy('production_number')->map(function ($val) {
                return $val->keyBy('stock_id');
            });
        return [
            'status' => 1,
            'msg' => $summary,
            'mutasi_masuk' => $mutasiMasuk,
            'mutasi_keluar' => $mutasiKeluar,
        ];
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
        $saleOrderId = $request->input('sales_order_id');
        $salesOrderNumber = $request->input('sales_order_number');
        $lawanCodeGroups = $request->input('lawan_code_group');
        $allSt = [];
        try {
            DB::beginTransaction();
            foreach ($stockIDs as $row => $stock_id) {
                $qty = format_db($quantitys[$row]);
                $unit = $units[$row];
                $flow = $flows[$row];
                $lawanCodeGroup = $lawanCodeGroups[$row];
                if ($lawanCodeGroup == $codeGroup) {
                    throw new \Exception('Lawan code group tidak boleh sama dengan code group');
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
                    ]), false);
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
                ]), false);
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
            DB::rollBack();
            return [
                'status' => 0,
                'msg' => $th->getMessage()
            ];
        }
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
            ->get();
        return [
            'status' => 1,
            'msg' => $kartu
        ];
    }

    public function mutasiStore(Request $request)
    {

        // return ['status' => 0, 'msg' => $request->all()];
        return KartuBDP::mutationStore($request);
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
        if (!$kartu->isHasKartuInvoice()) {
            $kartu->createDetailKartuInvoice();
        }
        return ['status' => 1, 'msg' => $kartu];
    }
}
