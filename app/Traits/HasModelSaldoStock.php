<?php

namespace App\Traits;

use App\Models\ChartAccount;
use App\Models\Journal;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;

trait HasModelSaldoStock
{
    public static function getTotalSaldoRupiah($date, $withProduction = false, $productionNumber = null,)
    {
        $indexDate = intval(createCarbon($date)->format('ymdHis000'));
        info('index date : ' . $indexDate);
        $saldo = static::query()->whereIn('index_date', function ($q) use ($indexDate, $withProduction, $productionNumber) {
            $q->select(DB::raw('max(index_date)'))
                ->from(with(new static)->getTable())
                ->where('book_journal_id', bookID())
                ->where('index_date', '<', $indexDate);
            if ($productionNumber) {
                $q->where('production_number', $productionNumber);
            }
            if ($withProduction)
                $q->groupBy('stock_id', 'production_number');
            else
                $q->groupBy('stock_id');
        })->sum('saldo_rupiah_total');
        return $saldo ? $saldo : 0;
    }

    public static function getTotalJournal($date)
    {
        $indexDate = createCarbon($date)->format('ymdHis00');
        $coa = ChartAccount::where('reference_model', static::class)->pluck('code_group')->all();
        $sub = Journal::select(DB::raw('max(index_date) as max_index_date'), 'code_group')
            ->where('index_date', '<', $indexDate)
            ->whereIn('code_group', $coa)
            ->groupBy('code_group');

        $journals = Journal::joinSub($sub, 'sub_journals', function ($q) {
            $q->on('journals.index_date', '=', 'sub_journals.max_index_date')
                ->on('journals.code_group', '=', 'sub_journals.code_group');
        })->sum('amount_saldo');
        return $journals ? $journals : 0;
    }

    public static function getSummaryProduction($year, $month)
    {
        $dateAwal = $year . '-' . $month . '-01 00:00:00';
        $indexDateAwal = intval(createCarbon($dateAwal)->format('ymdHis000'));
        $dateAkhir = $year . '-' . $month . '-' . dayInMonthQuantity($month, $year) . ' 23:59:59';
        $indexDateAkhir = intval(createCarbon($dateAkhir)->format('ymdHis000'));
        $saldoAkhir = static::query()->whereIn('index_date', function ($q) use ($indexDateAkhir) {
            $q->from(with(new static)->getTable())
                ->select(DB::raw('max(index_date)'))
                ->where('book_journal_id', bookID())
                ->where('index_date', '<', $indexDateAkhir)
                ->groupBy('stock_id', 'production_number');
        })->select('production_number', 'custom_stock_name', 'stock_id', 'saldo_qty_backend as saldo_qty_akhir', 'saldo_rupiah_total as saldo_rupiah_akhir', DB::raw('"0" as saldo_qty_awal'), DB::raw('"0" as saldo_rupiah_awal'))->get();
        $summary = static::query()->whereIn(with(new static)->getTable() . '.index_date', function ($q) use ($indexDateAwal) {
            $q->from(with(new static)->getTable())
                ->select(DB::raw('max(index_date)'))
                ->where('book_journal_id', bookID())
                ->where('index_date', '<', $indexDateAwal)
                ->groupBy('stock_id', 'production_number');
        })->select('production_number', 'custom_stock_name', 'stock_id', 'saldo_qty_backend as saldo_qty_awal', 'saldo_rupiah_total as saldo_rupiah_awal', DB::raw('"0" as saldo_qty_akhir'), DB::raw('"0" as saldo_rupiah_akhir'))->get();
        $mutasi = static::query()->whereBetween(with(new static)->getTable() . '.index_date', [$indexDateAwal, $indexDateAkhir])
            ->select(
                'production_number',
                'custom_stock_name',
                'stock_id',
                DB::raw('coalesce(mutasi_qty_backend,0) as mutasi'),
            )->get();
        $summary = collect($summary)->merge(collect($saldoAkhir))->merge(collect($mutasi));

        $dataStock = DB::table('stocks')->whereIn('stocks.id', $summary->pluck('stock_id')->all())->join('stock_categories', 'stocks.category_id', '=', 'stock_categories.id')
            ->join('stock_units', function ($join) {
                $join->on('stocks.id', '=', 'stock_units.stock_id')
                    ->on('stocks.unit_default', '=', 'stock_units.unit');
            })->select(
                'stocks.*',
                'stock_units.konversi as konversi',
                'stock_categories.name as category_name',
            )->get()->keyBy('id');
        $summary = $summary->groupBy('production_number')
            ->map(function ($dataspk, $number) use ($dataStock) {
                $values = collect($dataspk)->groupBy('stock_id')->map(function ($item, $stockid) use ($dataStock, $number) {
                    $data = []; //$dataStock[$stockid];
                    $name = $dataStock[$stockid]->name;
                    $customName = optional(collect($item)->filter(function ($val) use ($name) {
                        if ($val->custom_stock_name != $name) return true;
                    })->first())->custom_stock_name ?? "";
                    info($number . '-custom name : ' . $customName . ' data :' . collect($item)->pluck('custom_stock_name')->toJson());
                    $data['name'] = $customName ?: $name;
                    $data['konversi'] = $dataStock[$stockid]->konversi;
                    $data['category_name'] = $dataStock[$stockid]->category_name;
                    $data['unit_default'] = $dataStock[$stockid]->unit_default;
                    $data['id'] = $stockid;
                    $data['mutasi'] = collect($item)->map(function ($m) {
                        return $m->mutasi != 0 ? 1 : 0;
                    })->sum();
                    $data['saldo_qty_awal'] = collect($item)->sum('saldo_qty_awal');
                    $data['saldo_rupiah_awal'] = collect($item)->sum('saldo_rupiah_awal');
                    $data['saldo_qty_akhir'] = collect($item)->sum('saldo_qty_akhir');
                    $data['saldo_rupiah_akhir'] = collect($item)->sum('saldo_rupiah_akhir');
                    return $data;
                })->values();
                $isTampil = 0;
                foreach ($values as $val) {
                    if ($val['mutasi'] == 0 && $val['saldo_qty_awal'] == 0 && $val['saldo_rupiah_awal'] == 0 && $val['saldo_qty_akhir'] == 0 && $val['saldo_rupiah_akhir'] == 0) {
                    } else {
                        $isTampil = 1;
                        break;
                    }
                }
                if ($isTampil == 1)
                    return $values;
                else
                    return null;
            })->filter(function ($val) {
                if ($val) return true;
            });
        $mutasiMasuk = static::query()->whereBetween('created_at', [$dateAwal, $dateAkhir])
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
        $mutasiKeluar = static::query()->whereBetween('created_at', [$dateAwal, $dateAkhir])
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
            'month' => $month,
            'year' => $year
        ];
    }
}
