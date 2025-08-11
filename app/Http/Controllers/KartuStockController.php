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

    public static function getSummary($month = null, $year = null)
    {
        if (!$month)
            $month = getInput('month') ?? date('m');
        if (!$year)
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
        $stock= Stock::find($id);
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
        $view->title= $stock->name . ' [' . $stock->id . ']';
        $view->datas = $dataHistory;
        return $view;
    }
}
