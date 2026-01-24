<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ChartAccount;
use App\Models\ChartAccountAlias;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Chart\Chart;

class ChartAccountController extends Controller
{
    //

    public function index()
    {
        $view = view('master.chart-account');
        $charts = ChartAccount::withAlias()->get();

        foreach ($charts as $ca) {
            if ($ca->alias_id == null) {
                //belum ada alias. langsug create kan
                $ca->makeAlias();
            }
        }
        return $view;
    }

    public function store(Request $request)
    {
        return ChartAccount::createOrUpdate($request);
    }

    public function getItemChartAccount()
    {
        $searchs = [];
        if (getInput('search')) {
            $searchs = explode(' ', getInput('search'));
        }
        $charts = ChartAccount::aktif()->child()->withAlias();
        foreach ($searchs as $search) {
            $charts = $charts->whereRaw('coalesce(ca.name,chart_accounts.name) like?', ['%' . $search . '%']);
        }
        $charts = $charts->select(DB::raw('chart_accounts.code_group as id'), DB::raw('coalesce(ca.name,chart_accounts.name) as text'))->get();
        return [
            'results' => $charts
        ];
    }

    public function getItemChartAccountAll()
    {
        $searchs = [];
        if (getInput('search')) {
            $searchs = explode(' ', getInput('search'));
        }
        $charts = ChartAccount::aktif();
        foreach ($searchs as $search) {
            $charts = $charts->where('name', 'like', '%' . $search . '%');
        }
        $charts = $charts->select(DB::raw('code_group as id'), DB::raw('name as text'))->get();
        $alias = ChartAccountAlias::pluck('name', 'code_group')->all();
        $finalChart = $charts->map(function ($val) use ($alias) {
            if (array_key_exists($val['id'], $alias)) {
                $val['text'] = $alias[$val->id];
            }
            return $val;
        });
        return [
            'results' => $finalChart
        ];
    }
    public function getChartAccounts()
    {
        $charts = ChartAccount::aktif()->orderBy('code_group')->get()->groupBy('parent_id');
        $alias = ChartAccountAlias::get()->keyBy('code_group')->all();
        return [
            'status' => 1,
            'msg' => $charts,
            'alias' => $alias
        ];
    }

    public function getItemChartAccountKeuanganManual()
    {
        $kind = getInput('kind');
        $kinds = explode('|', $kind);
        $finalChart = collect([]);
        foreach ($kinds as $kind) {
            if ($kind == 'pendapatan') {
                $chart = ChartAccount::aktif()->child()->where('account_type', 'Pendapatan');
                if (getInput('search')) {
                    foreach (explode(' ', getInput('search')) as $search) {
                        $chart = $chart->where('name', 'like', '%' . $search . '%');
                    }
                }
                $chart = $chart->select(DB::raw('code_group as id'), DB::raw('name as text'))->get();
                $finalChart = $finalChart->merge($chart);
            }
            if ($kind == 'penjualan') {
                $chart = ChartAccount::aktif()->child()->whereBetween('code_group', [400000, 500000]);
                if (getInput('search')) {
                    foreach (explode(' ', getInput('search')) as $search) {
                        $chart = $chart->where('name', 'like', '%' . $search . '%');
                    }
                }
                $chart = $chart->select(DB::raw('code_group as id'), DB::raw('name as text'))->get();
                $finalChart = $finalChart->merge($chart);
            }
            if ($kind == 'piutang') {
                $chart = ChartAccount::aktif()->child()->where('reference_model', 'App\\Models\\KartuPiutang');
                if (getInput('search')) {
                    foreach (explode(' ', getInput('search')) as $search) {
                        $chart = $chart->where('name', 'like', '%' . $search . '%');
                    }
                }
                $chart = $chart->select(DB::raw('code_group as id'), DB::raw('name as text'))->get();
                $finalChart = $finalChart->merge($chart);
            }
            if ($kind == 'uang_muka_penjualan') {
                $chart = ChartAccount::aktif()->child()->where('reference_model', 'App\\Models\\KartuDPSales');
                if (getInput('search')) {
                    foreach (explode(' ', getInput('search')) as $search) {
                        $chart = $chart->where('name', 'like', '%' . $search . '%');
                    }
                }
                $chart = $chart->select(DB::raw('code_group as id'), DB::raw('name as text'))->get();
                $finalChart = $finalChart->merge($chart);
            }
            if ($kind == 'hutang') {
                $chart = ChartAccount::aktif()->child()->where('reference_model', 'App\\Models\\KartuHutang');
                if (getInput('search')) {
                    foreach (explode(' ', getInput('search')) as $search) {
                        $chart = $chart->where('name', 'like', '%' . $search . '%');
                    }
                }
                $chart = $chart->select(DB::raw('code_group as id'), DB::raw('name as text'))->get();
                $finalChart = $finalChart->merge($chart);
            }
            if ($kind == 'prepaid') {
                $chart = ChartAccount::aktif()->child()->where('reference_model', 'App\\Models\\KartuPrepaidExpense');
                if (getInput('search')) {
                    foreach (explode(' ', getInput('search')) as $search) {
                        $chart = $chart->where('name', 'like', '%' . $search . '%');
                    }
                }
                $chart = $chart->select(DB::raw('code_group as id'), DB::raw('name as text'))->get();
                $finalChart = $finalChart->merge($chart);
            }
            if ($kind == 'persediaan') {
                $chart = ChartAccount::aktif()->child()->whereBetween('code_group', [140000, 150000]);
                if (getInput('search')) {
                    foreach (explode(' ', getInput('search')) as $search) {
                        $chart = $chart->where('name', 'like', '%' . $search . '%');
                    }
                }
                $chart = $chart->select(DB::raw('code_group as id'), DB::raw('name as text'))->get();
                $finalChart = $finalChart->merge($chart);
            }
            if ($kind == 'kartu-inventory') {
                $chart = ChartAccount::aktif()->child()->where('reference_model', 'App\\Models\\KartuInventory');
                if (getInput('search')) {
                    foreach (explode(' ', getInput('search')) as $search) {
                        $chart = $chart->where('name', 'like', '%' . $search . '%');
                    }
                }
                $chart = $chart->select(DB::raw('code_group as id'), DB::raw('name as text'))->get();
                $finalChart = $finalChart->merge($chart);
            }
            if ($kind == 'inventory') {
                $chart = ChartAccount::aktif()->child()->where(function ($q) {
                    $q->whereBetween('code_group', [181000, 181999])->orWhere('code_group', 301000);
                });
                if (getInput('search')) {
                    foreach (explode(' ', getInput('search')) as $search) {
                        $chart = $chart->where('name', 'like', '%' . $search . '%');
                    }
                }
                $chart = $chart->select(DB::raw('code_group as id'), DB::raw('name as text'))->get();
                $finalChart = $finalChart->merge($chart);
            }
            if ($kind == 'akumulasi_inventory') {
                $chart = ChartAccount::aktif()->child()->where(function ($q) {
                    $q->whereBetween('code_group', [182000, 182999])->orWhere('code_group', 301000);
                });
                if (getInput('search')) {
                    foreach (explode(' ', getInput('search')) as $search) {
                        $chart = $chart->where('name', 'like', '%' . $search . '%');
                    }
                }
                $chart = $chart->select(DB::raw('code_group as id'), DB::raw('name as text'))->get();
                $finalChart = $finalChart->merge($chart);
            }
            if ($kind == 'beban_inventory') {
                $chart = ChartAccount::aktif()->child()->where(function ($q) {
                    $q->whereBetween('code_group', [800010, 800015])->orWhere('code_group', 301000);
                });
                if (getInput('search')) {
                    foreach (explode(' ', getInput('search')) as $search) {
                        $chart = $chart->where('name', 'like', '%' . $search . '%');
                    }
                }
                $chart = $chart->select(DB::raw('code_group as id'), DB::raw('name as text'))->get();
                $finalChart = $finalChart->merge($chart);
            }
            if ($kind == 'kas') {
                $chart = ChartAccount::aktif()->child()->where(function ($q) {
                    $q->whereBetween('code_group', [110000, 120000])->orWhere('code_group', 301000);
                });
                if (getInput('search')) {
                    foreach (explode(' ', getInput('search')) as $search) {
                        $chart = $chart->where('name', 'like', '%' . $search . '%');
                    }
                }
                $chart = $chart->select(DB::raw('code_group as id'), DB::raw('name as text'))->get();
                $finalChart = $finalChart->merge($chart);
            }
            if ($kind == 'beban') {
                $chart = ChartAccount::aktif()->child()->where('account_type', 'Beban');
                if (getInput('search')) {
                    foreach (explode(' ', getInput('search')) as $search) {
                        $chart = $chart->where('name', 'like', '%' . $search . '%');
                    }
                }
                $chart = $chart->select(DB::raw('code_group as id'), DB::raw('name as text'))->get();
                $finalChart = $finalChart->merge($chart);
            }
            if ($kind == 'pemindahan') {
                $chart = ChartAccount::aktif()->child()->where('name', 'Ayat Silang');
                if (getInput('search')) {
                    foreach (explode(' ', getInput('search')) as $search) {
                        $chart = $chart->where('name', 'like', '%' . $search . '%');
                    }
                }
                $chart = $chart->select(DB::raw('code_group as id'), DB::raw('name as text'))->get();
                $finalChart = $finalChart->merge($chart);
            }
            if ($kind == 'modal') {
                $chart = ChartAccount::aktif()->child()->where('account_type', 'Ekuitas');
                if (getInput('search')) {
                    foreach (explode(' ', getInput('search')) as $search) {
                        $chart = $chart->where('name', 'like', '%' . $search . '%');
                    }
                }
                $chart = $chart->select(DB::raw('code_group as id'), DB::raw('name as text'))->get();
                $finalChart = $finalChart->merge($chart);
            }
            if ($kind == 'lainlain') {
                $chart = ChartAccount::aktif()->child();
                if (getInput('search')) {
                    foreach (explode(' ', getInput('search')) as $search) {
                        $chart = $chart->where('name', 'like', '%' . $search . '%');
                    }
                }
                $chart = $chart->select(DB::raw('code_group as id'), DB::raw('name as text'))->get();
                $finalChart = $finalChart->merge($chart);
            }
        }
        $alias = ChartAccountAlias::pluck('name', 'code_group')->all();

        $finalChart = $finalChart->map(function ($val) use ($alias) {
            if (array_key_exists($val['id'], $alias)) {
                $val['text'] = $alias[$val->id];
            }
            return $val;
        });
        return [
            'results' => $finalChart
        ];
    }

    public function getItemChartAccountAsetTetap()
    {
        $chart = ChartAccount::where('code_group', 181000)->first();
        $charts = ChartAccount::where('parent_id', $chart->id);
        if (getInput('search')) {
            foreach (explode(' ', getInput('search')) as $search) {
                $charts = $charts->where('name', 'like', '%' . $search . '%');
            }
        }
        $charts = $charts->select(DB::raw('code_group as id'), DB::raw('name as text'))->get();
        return [
            'results' => $charts
        ];
    }


    public function getItemChartAccountBDD()
    {
        $chart = ChartAccount::where('code_group', 160000)->first();
        $charts = ChartAccount::where('parent_id', $chart->id);
        if (getInput('search')) {
            foreach (explode(' ', getInput('search')) as $search) {
                $charts = $charts->where('name', 'like', '%' . $search . '%');
            }
        }
        $charts = $charts->select(DB::raw('code_group as id'), DB::raw('name as text'))->get();
        return [
            'results' => $charts
        ];
    }

    public function getCodeGroupAccount($id)
    {

        $theFixCode = 0;
        $chart = ChartAccount::where('code_group', $id)->first();
        $code = $chart->code_group;
        for ($i = 1; $i < 10000000; $i *= 10) {
            if ($code % $i != 0) {
                $theFixCode = $code * 10 / $i;
                break;
            }
        }
        return [
            'status' => 1,
            'msg' => $theFixCode,
            'account_type' => $chart->account_type
        ];
    }

    public function getChartAccount($id)
    {
        $chart = ChartAccount::find($id);
        $parent = $chart->parent;
        return [
            'status' => 1,
            'msg' => $chart
        ];
    }

    public static function makeAlias(Request $request)
    {
        $codeGroups = $request->input('code_group');
        $codeGroup = implode("", $codeGroups);
        $name = $request->input('name');
        $chart = ChartAccount::where('code_group', $codeGroup)->first();
        $alias = ChartAccountAlias::createOrUpdate(new Request([
            'book_journal_id' => bookID(),
            'chart_account_id' => $chart->id,
            'code_group' => $codeGroup,
            'name' => $name
        ]));
        return [
            'status' => 1,
            'msg' => $alias
        ];
    }

    public function updateAllLevel()
    {
        $chartAccounts = ChartAccount::all();
        foreach ($chartAccounts as $chart) {
            $chart->updateLevel();
        }
        return [
            'status' => 1,
            'msg' => $chartAccounts
        ];
    }

    public function destroy($id)
    {
        $chart = ChartAccount::find($id);
        $chart->is_deleted = 1;
        $chart->deleted_at = Date('Y-m-d H:i:s');
        $chart->save();
        return [
            'status' => 1,
            'msg' => $chart
        ];
    }

    public function deleteAccount($id)
    {
        $chartAlias = ChartAccountAlias::find($id);
        $chartAlias->is_deleted = 1;
        $chartAlias->save();
        return [
            'status' => 1,
            'msg' => $chartAlias
        ];
    }
}
