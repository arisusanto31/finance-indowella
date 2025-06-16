<?php

namespace App\Traits;

use App\Models\ChartAccount;
use App\Models\Journal;
use Illuminate\Support\Facades\DB;

trait HasModelSaldoAset
{

    public static function getTotalSaldoRupiah($date, $kolomGroup = 'inventory_id')
    {
        $indexDate = createCarbon($date)->format('ymdHis000');
        $saldo = static::query()->whereIn('index_date', function ($q) use ($indexDate, $kolomGroup) {
            $q->select(DB::raw('max(index_date)'))
                ->from(with(new static)->getTable())
                ->where('book_journal_id', bookID())
                ->where('index_date', '<', $indexDate)
                ->groupBy($kolomGroup);
        })->get();
        $data = collect($saldo)->map(function ($item) use ($kolomGroup) {
            return collect($item)->only('nilai_buku', $kolomGroup, 'id');
        });
        info(static::class . ' ' . json_encode($data));
        $saldo = $saldo->sum('nilai_buku');
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
