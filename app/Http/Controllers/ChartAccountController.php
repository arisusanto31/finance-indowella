<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ChartAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChartAccountController extends Controller
{
    //

    public function index()
    {
        $view = view('master.chart-account');

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
        $charts = ChartAccount::aktif()->child();
        foreach ($searchs as $search) {
            $charts = $charts->where('name', 'like', '%' . $search . '%');
        }
        $charts = $charts->select(DB::raw('code_group as id'), DB::raw('name as text'))->get();
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
        return [
            'results' => $charts
        ];
    }
    public function getChartAccounts()
    {
        $charts = ChartAccount::aktif()->orderBy('code_group')->get()->groupBy('parent_id');
        return [
            'status' => 1,
            'msg' => $charts
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
        $chart = ChartAccount::find($id);
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
}
