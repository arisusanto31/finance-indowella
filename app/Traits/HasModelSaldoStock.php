<?php

namespace App\Traits;

use App\Models\ChartAccount;
use App\Models\Journal;
use Illuminate\Support\Facades\DB;

trait HasModelSaldoStock
{


    public static function getTotalSaldoRupiah($date, $productionNumber = null, $withProduction = false)
    {

        $indexDate = intval(createCarbon($date)->format('ymdHis000'));
        info('index date : '.$indexDate);
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
}
