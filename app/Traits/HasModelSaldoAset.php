<?php

namespace App\Traits;

use App\Models\ChartAccount;
use App\Models\ChartAccountAlias;
use App\Models\Inventory;
use App\Models\Journal;
use App\Models\PrepaidExpense;
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
        $coa = ChartAccountAlias::where('reference_model', static::class)->pluck('code_group')->all();
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

    public static function getSummary($year, $month, $kolomGroup = 'inventory_id')
    {
        // return $kolomGroup;
        if (!$year)
            $year = getInput('year') ? getInput('year') : date('Y');
        if(!$month)
            $month = getInput('month') ? toDigit(getInput('month'), 2) : date('m');
        if($kolomGroup=='inventory_id'){
            $parentClass=Inventory::class;
            $parentTable= 'inventories';
            $table= (new Static)->getTable();
            $typeParent= 'type_aset';
        }else if($kolomGroup=='prepaid_expense_id'){
            $parentClass=PrepaidExpense::class;
            $parentTable= 'prepaid_expenses';
            $table= (new Static)->getTable();
            $typeParent='type_bdd';
        }
        $indexLastYear = createCarbon($year . '-' . $month . '-01')->endOfMonth()->format('ymdHis000');
        $indexFirstYear = createCarbon($year . '-01-01')->startOfYear()->format('ymdHis000');
        $saldoBukuAwal = static::query()->whereIn('index_date', function ($q) use ($indexFirstYear,$table,$kolomGroup) {
            $q->select(DB::raw('max(index_date)'))->from($table)->where('book_journal_id', bookID())
                ->where('index_date', '<', $indexFirstYear)->groupBy($kolomGroup);
        })->where('nilai_buku', '>', 0)->select($kolomGroup, 'nilai_buku')->get()->keyBy($kolomGroup);
        $saldoBukuAkhir = static::query()->join($parentTable . ' as p', 'p.id', '=', $table . '.' . $kolomGroup)
            ->whereIn($table . '.index_date', function ($q) use ($indexLastYear, $table, $kolomGroup) {
                $q->from($table . ' as t')->where('t.book_journal_id', bookID())
                    ->where('index_date', '<', $indexLastYear)->select(DB::raw('max(index_date) as maxid'))->groupBy($kolomGroup);
            })->select($kolomGroup, 'nilai_buku', 'p.name')->get()->keyBy($kolomGroup);
        $idawal = collect($saldoBukuAwal)->keys()->toArray();
        $idakhir = collect($saldoBukuAkhir)->keys()->toArray();
        $inventoryAktif = array_unique(array_merge($idawal, $idakhir));
        $inv = $parentClass::from($parentTable . ' as inv')->whereIn('inv.id', $inventoryAktif)
            ->leftJoin($table . ' as ki', 'ki.' . $kolomGroup, '=', 'inv.id')
            ->where('ki.index_date', '<', $indexLastYear)
            ->where('ki.index_date', '>=', $indexFirstYear)
            ->where('ki.book_journal_id', bookID())

            ->select(
                'inv.id',
                'inv.name',
                'inv.' . $typeParent,
                'inv.keterangan_qty_unit',
                'inv.date',
                'inv.nilai_perolehan',
                'inv.periode',
                DB::raw('SUM( case when ki.amount>0 then ki.amount else 0 end) as total_pembelian'),
                DB::raw('SUM( case when ki.amount<0 then ki.amount else 0 end) as total_penyusutan'),
                DB::raw('date_format(ki.date, "%Y-%m") as bulan_susut'),
            )
            ->groupBy(DB::raw('date_format(ki.date,"%Y-%m")'), 'inv.id')->get()->groupBy($typeParent)
            ->map(function ($val) use($typeParent) {
                return collect($val)->groupBy('id')->map(function ($theval) use ($typeParent) {
                    return [
                        'id' => $theval[0]->id,
                        'name' => $theval[0]->name,
                        $typeParent => $theval[0]->$typeParent,
                        'keterangan_qty_unit' => $theval[0]->keterangan_qty_unit,
                        'date' => $theval[0]->date,
                        'nilai_perolehan' => $theval[0]->nilai_perolehan,
                        'total_pembelian' => $theval[0]->total_pembelian,
                        'periode' => $theval[0]->periode,
                        'total_penyusutan' => collect($theval)->sum('total_penyusutan'),
                        'penyusutan' => collect($theval)->pluck('total_penyusutan', 'bulan_susut')
                    ];
                });
            });

        return [
            'status' => 1,
            'msg' => $inv,
            'saldo_buku_awal' => $saldoBukuAwal,
            'saldo_buku_akhir' => $saldoBukuAkhir,
            'year' => $year,
            'month' => $month,
            'index_first_year' => $indexFirstYear,
            'index_last_year' => $indexLastYear
        ];
    }
}
