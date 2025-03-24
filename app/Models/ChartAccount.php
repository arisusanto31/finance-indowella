<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class ChartAccount extends Model
{
    //
    use HasFactory;

    protected $tables = 'chart_accounts';
    public $timestamps = true;


    public function parent()
    {
        return $this->belongsTo('App\Models\ChartAccount', 'parent_id');
    }
    public function childs()
    {
        return $this->hasMany('App\Models\ChartAccount', 'parent_id');
    }

    public function scopeAktif($q)
    {
        $q->where('chart_accounts.is_deleted', null);
    }

    public function aktifOn($q, $date)
    {

        $q->where(function ($que) use ($date) {
            $que->whereNull('chart_accounts.is_deleted')
                ->orWhere('chart_accounts.deleted_at', '>', $date);
        });
    }
    public function scopeChild($q)
    {
        $q->where('chart_accounts.is_child', true);
    }

    public static function createOrUpdate(Request $request)
    {
        try {

            $id = $request->input('id');
            $allAccounts = ['Aset', 'Kewajiban', 'Ekuitas', 'Pendapatan', 'Beban'];
            $chart = $id ? ChartAccount::find($id) : new ChartAccount;
            $chart->name = $request->input('name');
            $code = $request->input('code_group');
            $chart->code_group = implode("", $code);
            $chart->parent_id = $request->input('parent_id');
            $chart->account_type = $request->input('account_type');
            if (!in_array($chart->account_type, $allAccounts)) {
                return [
                    'status' => 0,
                    'msg' => 'tipe akun tidak valid'
                ];
            }
            $chart->save();
            $chart->updateLevel();
            return [
                'status' => 1,
                'msg' => $chart
            ];
        } catch (\Exception $e) {
            return [
                'status' => 0,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function updateLevel()
    {
        $level = 0;
        $parent = $this->parent;
        while ($parent) {
            $parent = $parent->parent;
            $level++;
            if ($level > 20) break;
        }

        if (count(value: $this->childs) > 0) {
            $this->is_child = false;
        } else {
            $this->is_child = true;
        }
        $this->level = $level;
        $this->save();
        return $this;
    }



    public function getSaldoAt($date)
    {
        $code = $this->code_group;
        for ($i = 1; $i < 10000000; $i *= 10) {
            if ($code % $i != 0) {
                $theFixCode = $code * 10 / $i;
                break;
            }
        }
        $indexDate = createCarbon($date)->format('ymdHis') . '00';
        $subquery = DB::table('journals')
            ->where('index_date', '<', $indexDate)
            ->where('code_group', 'like', $theFixCode . '%')
            ->select('code_group', DB::raw('MAX(index_date) as max_index_date'))
            ->groupBy('code_group');


        $saldo = DB::table('journals as j')
            ->joinSub($subquery, 'subquery', function ($join) {
                $join->on('j.code_group', '=', 'subquery.code_group')
                    ->on('j.index_date', '=', 'subquery.max_index_date');
            })->sum('j.amount_saldo');

        return $saldo;
    }

    public function getMutation($month, $year)
    {
        $lastDay = dayInMonthQuantity($month, '20' . $year);
        $theLastDate = $year . $month . $lastDay . '23595999';
        $startDate = $year . $month . '0000000000';
        $code = $this->code_group;
        for ($i = 1; $i < 10000000; $i *= 10) {
            if ($code % $i != 0) {
                $theFixCode = $code * 10 / $i;
                break;
            }
        }
        $journal = Journal::where('code_group', 'like', $theFixCode . '%')
            ->where('index_date', '<', (float)$theLastDate)
            ->where('index_date', '>', (float)$startDate)
            ->select(DB::raw('sum(amount_debet) as total_debet'), DB::raw('sum(amount_kredit) as total_kredit'))->first();
        return $journal;
    }
    public static function getLabaBulanAt($date)
    {

        $theLastDate = createCarbon($date)->format('ymdHis') . '00';

        $subquery = DB::table('journals')
            ->where('index_date', '<', (float)$theLastDate)->whereRaw('CONVERT(code_group, UNSIGNED) > ?', [400000])
            ->select('code_group', DB::raw('MAX(index_date) as max_index_date'))
            ->groupBy('code_group');

        $saldo = DB::table('journals as j')
            ->joinSub($subquery, 'subquery', function ($join) {
                $join->on('j.code_group', '=', 'subquery.code_group')
                    ->on('j.index_date', '=', 'subquery.max_index_date');
            })->sum('amount_saldo');
        return $saldo;
    }


    public static function getRincianLabaBulanAt($date)
    {

        $theLastDate = createCarbon($date)->format('ymdHis') . '00';

        $subquery = DB::table('journals')
            ->where('index_date', '<', (float)$theLastDate)->whereRaw('CONVERT(code_group, UNSIGNED) > ?', [400000])
            ->select('code_group', DB::raw('MAX(index_date) as max_index_date'))
            ->groupBy('code_group');

        $saldo = DB::table('journals as j')
            ->joinSub($subquery, 'subquery', function ($join) {
                $join->on('j.code_group', '=', 'subquery.code_group')
                    ->on('j.index_date', '=', 'subquery.max_index_date');
            })->rightJoin('chart_accounts as ca', 'ca.id', '=', 'j.chart_account_id')
            ->where('ca.code_group', '>=', 400000)
            ->select(
                'ca.name',
                'ca.id',
                'ca.code_group',
                DB::raw('coalesce(j.amount_saldo,0) as saldo_akhir'),
                'ca.is_child',
            )
            ->orderBy('ca.code_group')->get();

        $revisiSaldo = collect($saldo)
            ->map(function ($val) use ($saldo) {
                if ($val->is_child == 0) {
                    $code = Journal::getPrimaryCode($val->code_group);
                    $idchilds = ChartAccount::where('code_group', 'like', $code . '%')->pluck('id');
                    $val->saldo_akhir = $saldo->whereIn('id', $idchilds)->sum('saldo_akhir');
                }
                return $val;
            });
        return $revisiSaldo;
    }

    public static function getRincianNeracaAt($date)
    {
        try {
            $start = microtime(true);
            $theLastDate = createCarbon($date)->format('ymdHis') . '00';

            $subquery = DB::table('journals')
                ->where('index_date', '<', (float)$theLastDate)->whereRaw('CONVERT(code_group, UNSIGNED) < ?', [400000])
                ->select('code_group', DB::raw('MAX(index_date) as max_index_date'))
                ->groupBy('code_group');

            $saldo = DB::table('journals as j')
                ->joinSub($subquery, 'subquery', function ($join) {
                    $join->on('j.code_group', '=', 'subquery.code_group')
                        ->on('j.index_date', '=', 'subquery.max_index_date');
                })->rightJoin('chart_accounts as ca', 'ca.id', '=', 'j.chart_account_id')
                ->where('ca.code_group', '<', 400000)
                ->select(
                    'ca.name',
                    'ca.id',
                    'ca.account_type',
                    'ca.code_group',
                    'ca.level',
                    DB::raw('round(coalesce(j.amount_saldo,0),2) as saldo'),
                    'ca.is_child',
                )
                ->orderBy('ca.code_group')->get();

            $revisiSaldo = collect($saldo)
                ->map(function ($val) use ($saldo) {
                    if ($val->is_child == 0) {
                        $code = Journal::getPrimaryCode($val->code_group);
                        $idchilds = ChartAccount::where('code_group', 'like', $code . '%')->pluck('id');
                        $val->saldo = round($saldo->whereIn('id', $idchilds)->sum('saldo'), 2);
                    }
                    return $val;
                })
                ->filter(function ($val) {
                    if ($val->level == 1)
                        return true;
                })->groupBy('account_type');
            return [
                'status' => 1,
                'msg' => $revisiSaldo,
                'time' => microtime(true) - $start
            ];
        } catch (Throwable $th) {
            return [
                'status' => 0,
                'msg' => $th->getMessage()
            ];
        }
    }

    public static function getRincianSaldoNeracaLajur($month, $year)
    {
        $starttime = microtime(true);
        $date = createCarbon($year . '-' . $month . '-01 00:00:00');
        $firstdate = $date->format('ymdHis') . '00';
        $lastdate = $date->addMonth()->format('ymdHis') . '00';
        $subquery = DB::table('journals')
            ->where('index_date', '<', (float)$firstdate)
            ->select('code_group', DB::raw('MAX(index_date) as max_index_date'))
            ->groupBy('code_group');

        $saldo = DB::table('journals as j')
            ->joinSub($subquery, 'subquery', function ($join) {
                $join->on('j.code_group', '=', 'subquery.code_group')
                    ->on('j.index_date', '=', 'subquery.max_index_date');
            })->rightJoin('chart_accounts as ca', 'ca.id', '=', 'j.chart_account_id')
            ->select(
                'ca.name',
                'ca.id',
                'ca.code_group',
                DB::raw('coalesce(j.amount_saldo,0) as saldo_akhir'),
                'ca.is_child',
            )
            ->orderBy('ca.code_group')->get();

        $saldoAwal = collect($saldo)
            ->map(function ($val) use ($saldo) {
                if ($val->is_child == 0) {
                    $code = Journal::getPrimaryCode($val->code_group);
                    $idchilds = ChartAccount::where('code_group', 'like', $code . '%')->pluck('id');
                    $val->saldo_akhir = $saldo->whereIn('id', $idchilds)->sum('saldo_akhir');
                }
                return $val;
            })->keyBy('id');

        $subquery = DB::table('journals')
            ->where('index_date', '<', (float)$lastdate)
            ->select('code_group', DB::raw('MAX(index_date) as max_index_date'))
            ->groupBy('code_group');

        $saldo_akhir = DB::table('journals as j')
            ->joinSub($subquery, 'subquery', function ($join) {
                $join->on('j.code_group', '=', 'subquery.code_group')
                    ->on('j.index_date', '=', 'subquery.max_index_date');
            })->rightJoin('chart_accounts as ca', 'ca.id', '=', 'j.chart_account_id')
            ->select(
                'ca.name',
                'ca.id',
                'ca.code_group',
                DB::raw('coalesce(j.amount_saldo,0) as saldo_akhir'),
                'ca.is_child',
            )
            ->orderBy('ca.code_group')->get();

        $saldoAkhir = collect($saldo_akhir)
            ->map(function ($val) use ($saldo_akhir) {
                if ($val->is_child == 0) {
                    $code = Journal::getPrimaryCode($val->code_group);
                    $idchilds = ChartAccount::where('code_group', 'like', $code . '%')->pluck('id');
                    $val->saldo_akhir = round($saldo_akhir->whereIn('id', $idchilds)->sum('saldo_akhir'), 2);
                }
                return $val;
            })->keyBy('id');


        $fixdatas = ChartAccount::select('name', 'account_type', 'id', 'code_group', 'level')->orderBy('code_group')->get()
            ->map(function ($val) use ($saldoAkhir, $saldoAwal) {
                $val['saldo_awal'] = $saldoAwal[$val->id]->saldo_akhir;
                $val['saldo_akhir'] = $saldoAkhir[$val->id]->saldo_akhir;
                return $val;
            });
        return [
            'status' => 1,
            'msg' => $fixdatas,
            'firstdate' => $firstdate,
            'lastdate' => $lastdate,
            'time' => microtime(true) - $starttime
        ];
    }


    public static function getRincianMutationNeracaLajur($month, $year)
    {
        $starttime = microtime(true);
        $date = createCarbon($year . '-' . $month . '-01 00:00:00');
        $firstdate = $date->format('ymdHis') . '00';
        $lastdate = $date->addMonth()->format('ymdHis') . '00';
        $chartAccount = ChartAccount::select('id', 'code_group', 'is_child', 'level')->get();
        $mutasi_ = DB::table('journals as j')

            ->whereBetween('j.index_date', [(float)$firstdate, (float)$lastdate])
            ->rightJoin('chart_accounts as c', 'c.id', '=', 'j.chart_account_id')
            ->select(
                DB::raw('sum(j.amount_kredit) as total_kredit'),
                DB::raw('sum(j.amount_debet) as total_debet'),
                'c.code_group',
                'c.id',
                'c.is_child',
                'c.name'
            )->orderBy('c.code_group')->groupBy('c.code_group')->get();
        $fixMutasi = [];
        foreach ($chartAccount as $data) {
            $newdata = [];

            if ($data->is_child == 0) {
                $code = Journal::getPrimaryCode($data->code_group);
                $idchilds = ChartAccount::where('code_group', 'like', $code . '%')->pluck('id');
            } else {
                $idchilds = [$data->id];
            }
            $newdata['id'] = $data->id;
            $newdata['total_debet'] = round($mutasi_->whereIn('id', $idchilds)->sum('total_debet'), 2);
            $newdata['total_kredit'] = round($mutasi_->whereIn('id', $idchilds)->sum('total_kredit'), 2);
            $fixMutasi[$data->id] = $newdata;
        }


        return [
            'status' => 1,
            'msg' => $fixMutasi,
            'firstdate' => $firstdate,
            'lastdate' => $lastdate,
            'time' => microtime(true) - $starttime
        ];
    }
}
