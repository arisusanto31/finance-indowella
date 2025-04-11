<?php

namespace App\Http\Controllers;

use App\Models\KartuStock;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KartuStockController extends Controller
{

    public function index()
    {
        return view('kartu.kartu-stock');
    }

    public function getSummary()
    {
        $month = getInput('month') ?? date('m');
        $year = getInput('year') ?? date('Y');
        $dateAwal = $year . '-' . $month . '-01 00:00:00';
        $dateAkhir = $year . '-' . $month . '-' . dayInMonthQuantity($month, $year) . ' 23:59:59';
        $saldoAwal = kartuStock::whereIn('id', function ($q) use ($dateAwal) {
            $q->from('kartu_stocks')
                ->select(DB::raw('max(id)'))
                ->where('book_journal_id', session('book_journal_id'))
                ->where('created_at', '<', $dateAwal)
                ->groupBy('stock_id');
        });
        $saldoAkhir = kartuStock::whereIn('id', function ($q) use ($dateAkhir) {
            $q->from('kartu_stocks')
                ->select(DB::raw('max(id)'))
                ->where('book_journal_id', session('book_journal_id'))
                ->where('created_at', '<', $dateAkhir)
                ->groupBy('stock_id');
        });
        $stock = Stock::leftJoinSub($saldoAwal, 'saldoAwal', function ($join) {
            $join->on('stocks.id', '=', 'saldoAwal.stock_id');
        })->leftJoinSub($saldoAkhir, 'saldoAkhir', function ($join) {
            $join->on('stocks.id', '=', 'saldoAkhir.stock_id');
        })->join('stock_categories', 'stocks.category_id', '=', 'stock_categories.id')
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


        $mutasiMasuk = KartuStock::whereBetween('created_at', [$dateAwal, $dateAkhir])
            ->where('mutasi_qty_backend', '>', 0)
            ->select(
                DB::raw('sum(coalesce(mutasi_qty_backend,0)) as qty'),
                DB::raw('sum(coalesce(mutasi_rupiah_on_unit,0)) as rupiah_unit'),
                DB::raw('sum(coalesce(mutasi_rupiah_total,0)) as total'),
                DB::raw('max(stock_id) as stock_id')

            )->groupBy('stock_id')
            ->get()->keyBy('stock_id');
        $mutasiKeluar = KartuStock::whereBetween('created_at', [$dateAwal, $dateAkhir])
            ->where('mutasi_qty_backend', '<', 0)
            ->select(
                DB::raw('sum(mutasi_qty_backend) as qty'),
                DB::raw('sum(mutasi_rupiah_on_unit) as rupiah_unit'),
                DB::raw('sum(mutasi_rupiah_total) as total'),
                DB::raw('max(stock_id) as stock_id')

            )->groupBy('stock_id')
            ->get()->keyBy('stock_id');
        return [
            'status' => 1,
            'msg' => $stock,
            'mutasi_masuk' => $mutasiMasuk,
            'mutasi_keluar' => $mutasiKeluar,
        ];
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
}
