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


    protected $fillable = [
        'id',
        'name',
        'code_group',
        'parent_id',
        'account_type',
        'reference_model',
        'is_child',
        'level'
    ];
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
        $caids = ChartAccount::join('chart_account_aliases as ca', function ($join) {
            $join->on('ca.code_group', '=', 'chart_accounts.code_group')
                ->on('ca.book_journal_id', '=', DB::raw(bookID()));
        })->where('chart_accounts.is_deleted', null)
            ->where('ca.is_deleted', false)
            ->pluck('chart_accounts.id')->toArray();

        $q->whereIn('chart_accounts.id', $caids);
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
            $parentCode = $request->input('parent_id');
            $chartParent = ChartAccount::where('code_group', $parentCode)->first();
            $chart->parent_id = $chartParent ? $chartParent->id : null;
            $chart->account_type = $request->input('account_type');
            $chart->reference_model = $request->input('reference_model');
            if (!in_array($chart->account_type, $allAccounts)) {
                return [
                    'status' => 0,
                    'msg' => 'tipe akun tidak valid'
                ];
            }
            $chart->save();
            $chart->updateLevel();
            $chart->makeAlias();
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

    public static function createNewChildChart(Request $request)
    {
        try {
            $codeGroup = $request->input('code_group');
            $name = $request->input('name');

            $parent = null;
            $codes = str_split($codeGroup);
            while ($parent == null && count($codes) > 1) {
                array_pop($codes);
                $parentCode = implode("", $codes);
                for ($i = 0; $i < 6 - count($codes); $i++) {
                    $parentCode .= '0';
                }
                info('mencari parent code ' . $parentCode);
                $parent = ChartAccount::where('code_group', $parentCode)->first();
                if($parent){
                    info('ketemu parent code ' . $parentCode);
                    break;
                }else{
                    info('tidak ketemu parent code ' . $parentCode);
                }
            }
            if ($parent == null) {
                return [
                    'status' => 0,
                    'msg' => 'code ' . $codeGroup . ' harus dibuat manual dulu '
                ];
            }

            $chart = new ChartAccount();
            $chart->name = $name;
            $chart->code_group = $codeGroup;
            $chart->account_type = $parent->account_type;
            $chart->reference_model = $parent->reference_model;
            $chart->parent_id = $parent->id;
            $chart->save();
            $chart->updateLevel();
            $chart->makeAlias();
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

    public function makeAlias()
    {
        $alias = ChartAccountAlias::where('chart_account_id', $this->id)->where('book_journal_id', bookID())->first();
        if (!$alias) {
            $alias = new ChartAccountAlias();
            $alias->book_journal_id = bookID();
            $alias->chart_account_id = $this->id;
            $alias->code_group = $this->code_group;
            $alias->name = $this->name;
            $alias->is_child = $this->is_child;
            $alias->level = $this->level;
            $alias->reference_model = $this->reference_model;
            $alias->account_type = $this->account_type;
            $alias->save();
        }
        return $alias;
    }

    public function scopeWithAlias($q)
    {
        $q->leftJoin('chart_account_aliases as ca', function ($join) {
            $join->on('ca.chart_account_id', '=', 'chart_accounts.id')
                ->on('ca.book_journal_id', '=', DB::raw(bookID()));
        })->where(function ($q) {
            $q->where('ca.book_journal_id', bookID())
                ->orWhereNull('ca.id');
        })
            ->select('chart_accounts.*', DB::raw('coalesce(ca.name,chart_accounts.name) as alias_name'), 'ca.id as alias_id');
        return $q;
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


        $saldo = Journal::from('journals as j')
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

        $subquery = Journal::where('index_date', '<', (float)$theLastDate)->whereRaw('CONVERT(code_group, UNSIGNED) > ?', [400000])
            ->select('code_group', DB::raw('MAX(index_date) as max_index_date'))
            ->groupBy('code_group');

        $saldo = Journal::from('journals as j')
            ->joinSub($subquery, 'subquery', function ($join) {
                $join->on('j.code_group', '=', 'subquery.code_group')
                    ->on('j.index_date', '=', 'subquery.max_index_date');
            })->sum('amount_saldo');
        return $saldo;
    }


    public static function getRincianLabaBulanAt($date, $tokoid = null)
    {

        if ($tokoid == null) {
            //langsung ambil dari saldo terakhir yang tertulis di database
            $theLastDate = createCarbon($date)->format('ymdHis') . '00';
            $subquery = Journal::where('index_date', '<', (float)$theLastDate)->whereRaw('CONVERT(code_group, UNSIGNED) > ?', [400000])
                ->select('code_group as subquery_code_group', DB::raw('MAX(index_date) as max_index_date'))
                ->groupBy('code_group');
            $thejournal = Journal::from('journals as j')
                ->joinSub($subquery, 'subquery', function ($join) {
                    $join->on('j.code_group', '=', 'subquery.subquery_code_group')
                        ->on('j.index_date', '=', 'subquery.max_index_date');
                });
            $saldo =
                ChartAccountAlias::from('chart_account_aliases as ca')
                ->leftJoinSub($thejournal, 'j', function ($join) {
                    $join->on('j.code_group', '=', 'ca.code_group');
                })
                ->where('ca.code_group', '>=', 400000)
                ->where('ca.is_child', 1)
                ->select(
                    'ca.name',
                    'ca.id',
                    'ca.is_deleted',
                    'ca.code_group',
                    DB::raw('coalesce(j.amount_saldo,0) as saldo_akhir'),
                    'ca.is_child',
                )
                ->orderBy('ca.code_group')->get();

            $revisiSaldo = collect($saldo)
                ->map(function ($val) use ($saldo) {
                    if ($val->is_child == 0) {
                        $code = Journal::getPrimaryCode($val->code_group);
                        $idchilds = ChartAccountAlias::where('code_group', 'like', $code . '%')->pluck('code_group');
                        $val->saldo_akhir = $saldo->whereIn('code_group', $idchilds)->sum('saldo_akhir');
                    }
                    return $val;
                });

            return $revisiSaldo;
        } else {
            $indexAwal = createCarbon($date)->format('ym01His') . '00';
            $indexAkhir = createCarbon($date)->format('ymd23595999');
            //kalo pake toko id harus ngitung manual nih ,ahahahy
            $journals = Journal::from('journals as j')->where('j.code_group', '>', 400000)
                ->where('j.toko_id', $tokoid)->whereBetween('j.index_date', [$indexAwal, $indexAkhir]);

            $saldo = ChartAccountAlias::from('chart_account_aliases as ca')->where('ca.code_group', '>', 400000)
                ->where('ca.is_deleted', false)
                ->where('ca.is_child', 1)->leftJoinSub($journals, 'j', function ($join) {
                    $join->on('j.code_group', '=', 'ca.code_group');
                })->select(
                    DB::raw('coalesce(sum(j.amount_kredit- j.amount_debet),0) as saldo_akhir'),
                    'ca.code_group',
                    'ca.name',
                    'ca.id',
                    'ca.is_child',
                )->groupBy('code_group')->get();
            $revisiSaldo = collect($saldo)
                ->map(function ($val) use ($saldo) {
                    if ($val->is_child == 0) {
                        $code = Journal::getPrimaryCode($val->code_group);
                        $idchilds = ChartAccountAlias::where('code_group', 'like', $code . '%')->pluck('code_group');
                        $val->saldo_akhir = $saldo->whereIn('code_group', $idchilds)->sum('saldo_akhir');
                    }
                    return $val;
                });
            return [];
            return $revisiSaldo;
        }
    }

    public static function getRincianNeracaAt($date)
    {
        try {
            $start = microtime(true);
            $theLastDate = createCarbon($date)->format('ymdHis') . '00';
            $subquery = Journal::where('index_date', '<', (float)$theLastDate)->whereRaw('CONVERT(code_group, UNSIGNED) < ?', [400000])
                ->select('code_group as scode_group', DB::raw('MAX(index_date) as max_index_date'))
                ->groupBy('code_group');
            $thejournal = Journal::from('journals as j')
                ->joinSub($subquery, 'subquery', function ($join) {
                    $join->on('j.code_group', '=', 'subquery.scode_group')
                        ->on('j.index_date', '=', 'subquery.max_index_date');
                });

            $saldo =  ChartAccountAlias::from('chart_account_aliases as ca')
                ->join('chart_accounts as c', 'c.code_group', '=', 'ca.code_group')
                ->leftJoinSub($thejournal, 'j', function ($join) {
                    $join->on('j.code_group', '=', 'ca.code_group');
                })
                ->where('ca.code_group', '<', 400000)
                ->select(
                    'ca.name',
                    'ca.id',
                    'c.account_type',
                    'ca.code_group',
                    'c.level',
                    DB::raw('round(coalesce(j.amount_saldo,0),2) as saldo'),
                    'c.is_child',
                )
                ->orderBy('ca.code_group')->get();
            $revisiSaldo = collect($saldo)
                ->map(function ($val) use ($saldo) {
                    if ($val->is_child == 0 && $val->level == 1) {
                        $code = Journal::getPrimaryCode($val->code_group);
                        $idchilds = ChartAccountAlias::where('code_group', 'like', $code . '%')->pluck('code_group');
                        $val->saldo = round($saldo->whereIn('code_group', $idchilds)->sum('saldo'), 2);
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
        $subquery = Journal::where('index_date', '<', (float)$firstdate)
            ->select('code_group as scode_group', DB::raw('MAX(index_date) as max_index_date'))
            ->groupBy('code_group');

        $thejournal = Journal::from('journals as j')
            ->joinSub($subquery, 'subquery', function ($join) {
                $join->on('j.code_group', '=', 'subquery.scode_group')
                    ->on('j.index_date', '=', 'subquery.max_index_date');
            });

        $saldo =  ChartAccountAlias::from('chart_account_aliases as ca')
            ->leftJoinSub($thejournal, 'j', function ($join) {
                $join->on('j.code_group', '=', 'ca.code_group');
            })
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
                    $idchilds = ChartAccountAlias::where('code_group', 'like', $code . '%')->pluck('code_group');
                    $val->saldo_akhir = $saldo->whereIn('code_group', $idchilds)->sum('saldo_akhir');
                }
                return $val;
            })->keyBy('code_group');

        $subquery = Journal::from('journals as j')
            ->where('index_date', '<', (float)$lastdate)
            ->select('code_group as scode_group', DB::raw('MAX(index_date) as max_index_date'))
            ->groupBy('code_group');

        $thejournal = Journal::from('journals as j')
            ->joinSub($subquery, 'subquery', function ($join) {
                $join->on('j.code_group', '=', 'subquery.scode_group')
                    ->on('j.index_date', '=', 'subquery.max_index_date');
            });

        $saldo_akhir =  ChartAccountAlias::from('chart_account_aliases as ca')
            ->leftJoinSub($thejournal, 'j', function ($join) {
                $join->on('j.code_group', '=', 'ca.code_group');
            })
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
                    $idchilds = ChartAccountAlias::where('code_group', 'like', $code . '%')->pluck('code_group');
                    $val->saldo_akhir = round($saldo_akhir->whereIn('code_group', $idchilds)->sum('saldo_akhir'), 2);
                }
                return $val;
            })->keyBy('code_group');


        $fixdatas = ChartAccountAlias::where('is_deleted', false)->select('name', 'account_type', 'id', 'code_group', 'level')->orderBy('code_group')->get()
            ->map(function ($val) use ($saldoAkhir, $saldoAwal) {
                $val['saldo_awal'] = array_key_exists($val->code_group, $saldoAwal->all()) ? money($saldoAwal[$val->code_group]->saldo_akhir) : 0;
                $val['saldo_akhir'] = array_key_exists($val->code_group, $saldoAkhir->all()) ? money($saldoAkhir[$val->code_group]->saldo_akhir) : 0;
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
        $chartAccount = ChartAccountAlias::select('id', 'code_group', 'is_child', 'level')->get();
        $subquery = Journal::from('journals as j')->whereBetween('j.index_date', [(float)$firstdate, (float)$lastdate]);
        $mutasi_ = ChartAccountAlias::from('chart_account_aliases as c')
            ->leftJoinSub($subquery, 'j', function ($join) {
                $join->on('j.code_group', '=', 'c.code_group');
            })
            ->select(
                DB::raw('sum(j.amount_kredit) as total_kredit'),
                DB::raw('sum(j.amount_debet) as total_debet'),
                'c.code_group',
                'c.id',
                'c.is_child',
                'c.name'
            )->orderBy('c.code_group')->groupBy('c.code_group', 'c.id', 'c.is_child', 'c.name')->get();
        $fixMutasi = [];
        foreach ($chartAccount as $data) {
            $newdata = [];

            if ($data->is_child == 0) {
                $code = Journal::getPrimaryCode($data->code_group);
                $idchilds = ChartAccountAlias::where('code_group', 'like', $code . '%')->pluck('code_group');
            } else {
                $idchilds = [$data->code_group];
            }
            $newdata['code_group'] = $data->code_group;
            $newdata['total_debet'] = money(round($mutasi_->whereIn('code_group', $idchilds)->sum('total_debet'), 2));
            $newdata['total_kredit'] = money(round($mutasi_->whereIn('code_group', $idchilds)->sum('total_kredit'), 2));
            $fixMutasi[$data->code_group] = $newdata;
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
