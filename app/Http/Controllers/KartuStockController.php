<?php

namespace App\Http\Controllers;

use App\Models\DetailKartuInvoice;
use App\Models\Journal;
use App\Models\KartuStock;
use App\Models\Stock;
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

    public function getSummary()
    {
        $month = getInput('month') ?? date('m');
        $year = getInput('year') ?? date('Y');
        $dateAwal = $year . '-' . $month . '-01 00:00:00';
        $dateAkhir = $year . '-' . $month . '-' . dayInMonthQuantity($month, $year) . ' 23:59:59';
        $saldoAwal = kartuStock::whereIn('index_date', function ($q) use ($dateAwal) {
            $q->from('kartu_stocks')
                ->select(DB::raw('max(index_date)'))
                ->where('book_journal_id', bookID())
                ->where('created_at', '<', $dateAwal)
                ->groupBy('stock_id');
        });
        $saldoAkhir = kartuStock::whereIn('index_date', function ($q) use ($dateAkhir) {
            $q->from('kartu_stocks')
                ->select(DB::raw('max(index_date)'))
                ->where('book_journal_id', bookID())
                ->where('created_at', '<', $dateAkhir)
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


        $mutasiMasuk = KartuStock::whereBetween('created_at', [$dateAwal, $dateAkhir])
            ->where('mutasi_qty_backend', '>', 0)
            ->select(
                DB::raw('sum(coalesce(mutasi_qty_backend,0)) as qty'),

                DB::raw('sum(coalesce(mutasi_rupiah_total,0)) as total'),
                DB::raw('max(stock_id) as stock_id')

            )->groupBy('stock_id')
            ->get()->keyBy('stock_id');
        $mutasiKeluar = KartuStock::whereBetween('created_at', [$dateAwal, $dateAkhir])
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
            )->orderBy('ks.index_date','asc')->get();
        $stock= Stock::find($stockid);
        $view->data = $kartuStocks;
        $view->stock= $stock;
        return $view;
    }
}
