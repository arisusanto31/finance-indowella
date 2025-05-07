<?php

namespace App\Http\Controllers;

use App\Models\KartuBDP;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
                ->groupBy('stock_id', 'spk_number');
        })->select('spk_number', 'stock_id', 'saldo_qty_backend as saldo_qty_awal', 'saldo_rupiah_total as saldo_rupiah_awal', DB::raw('"0" as saldo_qty_akhir'), DB::raw('"0" as saldo_rupiah_akhir'));

        $summary = KartuBDP::whereIn('kartu_bdps.id', function ($q) use ($dateAwal) {
            $q->from('kartu_bdps')
                ->select(DB::raw('max(id)'))
                ->where('book_journal_id', session('book_journal_id'))
                ->where('created_at', '<', $dateAwal)
                ->groupBy('stock_id', 'spk_number');
        })->select('spk_number', 'stock_id', 'saldo_qty_backend as saldo_qty_akhir', 'saldo_rupiah_total as saldo_rupiah_akhir', DB::raw('"0" as saldo_qty_awal'), DB::raw('"0" as saldo_rupiah_awal'))
            ->union($saldoAkhir)->get();

        $dataStock = Stock::whereIn('stocks.id', $summary->pluck('stock_id')->all())->join('stock_categories', 'stocks.category_id', '=', 'stock_categories.id')
            ->join('stock_units', function ($join) {
                $join->on('stocks.id', '=', 'stock_units.stock_id')
                    ->on('stocks.unit_default', '=', 'stock_units.unit');
            })->select(
                'stocks.*',
                'stock_units.konversi as konversi',
                'stock_categories.name as category_name',
            )->get()->keyBy('id');

        $summary = $summary->groupBy('spk_number')
            ->map(function ($dataspk) use ($dataStock) {
                return collect($dataspk)->groupBy('stock_id')->map(function ($item, $stockid) use ($dataStock) {
                    $data = []; //$dataStock[$stockid];
                    $data['name'] = $dataStock[$stockid]->name;
                    $data['konversi'] = $dataStock[$stockid]->konversi;
                    $data['category_name'] = $dataStock[$stockid]->category_name;

                    $data['unit_default'] = $dataStock[$stockid]->unit_default;
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
                'spk_number'

            )->groupBy('stock_id', 'spk_number')
            ->get()->groupBy('spk_number')->map(function ($val) {
                return $val->keyBy('stock_id');
            });
        $mutasiKeluar = KartuBDP::whereBetween('created_at', [$dateAwal, $dateAkhir])
            ->where('mutasi_qty_backend', '<', 0)
            ->select(
                DB::raw('sum(mutasi_qty_backend) as qty'),
                DB::raw('sum(mutasi_rupiah_on_unit) as rupiah_unit'),
                DB::raw('sum(mutasi_rupiah_total) as total'),
                DB::raw('max(stock_id) as stock_id'),
                'spk_number'

            )->groupBy('stock_id', 'spk_number')
            ->get()->groupBy('spk_number')->map(function ($val) {
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

        return KartuBDP::mutationStore($request);
    }
}
