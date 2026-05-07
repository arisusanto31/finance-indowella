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
            }else{
                $ca->updateAlias();
            }
        
        }
        $allaliasID= collect($charts)->pluck('alias_id')->all();
        $aliases= ChartAccountAlias::whereIn('id',$allaliasID)->get();
        foreach($aliases as $al){
            $al->updateLevel();
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
        $charts = ChartAccount::aktif()->withAlias();
        foreach ($searchs as $search) {
            $charts = $charts->whereRaw('coalesce(ca.name,chart_accounts.name) like ?', ['%' . $search . '%']);
        }
        $charts = $charts->select(DB::raw('chart_accounts.code_group as id'), DB::raw('coalesce(ca.name,chart_accounts.name) as text'))->get();
        $alias = ChartAccountAlias::pluck('name', 'code_group')->all();
        $finalChart = $charts->map(function ($val) use ($alias) {
            if (array_key_exists($val['id'], $alias)) {
                $val['text'] = $val['id'].' - '.$alias[$val['id']];
            }
            return $val;
        });
        return [
            'results' => $finalChart
        ];
    }
    public function getChartAccounts()
    {
        $charts = ChartAccountAlias::orderBy('code_group')->get()->groupBy('parent_id');
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
                $chart = ChartAccountAlias::aktif()->child()->where('account_type', 'Pendapatan');
                if (getInput('search')) {
                    foreach (explode(' ', getInput('search')) as $search) {
                        $chart = $chart->where('name', 'like', '%' . $search . '%');
                    }
                }
                $chart = $chart->select(DB::raw('code_group as id'), DB::raw('name as text'))->get();
                $finalChart = $finalChart->merge($chart);
            }
            if ($kind == 'penjualan') {
                $chart = ChartAccountAlias::aktif()->child()->whereBetween('code_group', [400000, 500000]);
                if (getInput('search')) {
                    foreach (explode(' ', getInput('search')) as $search) {
                        $chart = $chart->where('name', 'like', '%' . $search . '%');
                    }
                }
                $chart = $chart->select(DB::raw('code_group as id'), DB::raw('name as text'))->get();
                $finalChart = $finalChart->merge($chart);
            }
            if ($kind == 'piutang') {
                $chart = ChartAccountAlias::aktif()->child()->where('reference_model', 'App\\Models\\KartuPiutang');
                if (getInput('search')) {
                    foreach (explode(' ', getInput('search')) as $search) {
                        $chart = $chart->where('name', 'like', '%' . $search . '%');
                    }
                }
                $chart = $chart->select(DB::raw('code_group as id'), DB::raw('name as text'))->get();
                $finalChart = $finalChart->merge($chart);
            }
            if ($kind == 'uang_muka_penjualan') {
                $chart = ChartAccountAlias::aktif()->child()->where('reference_model', 'App\\Models\\KartuDPSales');
                if (getInput('search')) {
                    foreach (explode(' ', getInput('search')) as $search) {
                        $chart = $chart->where('name', 'like', '%' . $search . '%');
                    }
                }
                $chart = $chart->select(DB::raw('code_group as id'), DB::raw('name as text'))->get();
                $finalChart = $finalChart->merge($chart);
            }
            if ($kind == 'uang_muka_pembelian') {
                $chart = ChartAccountAlias::aktif()->child()->where('reference_model', 'App\\Models\\KartuDPPurchase');
                if (getInput('search')) {
                    foreach (explode(' ', getInput('search')) as $search) {
                        $chart = $chart->where('name', 'like', '%' . $search . '%');
                    }
                }
                $chart = $chart->select(DB::raw('code_group as id'), DB::raw('name as text'))->get();
                $finalChart = $finalChart->merge($chart);
            }
            if ($kind == 'hutang') {
                $chart = ChartAccountAlias::aktif()->child()->where('reference_model', 'App\\Models\\KartuHutang');
                if (getInput('search')) {
                    foreach (explode(' ', getInput('search')) as $search) {
                        $chart = $chart->where('name', 'like', '%' . $search . '%');
                    }
                }
                $chart = $chart->select(DB::raw('code_group as id'), DB::raw('name as text'))->get();
                $finalChart = $finalChart->merge($chart);
            }
            if ($kind == 'prepaid') {
                $chart = ChartAccountAlias::aktif()->child()->where('reference_model', 'App\\Models\\KartuPrepaidExpense');
                if (getInput('search')) {
                    foreach (explode(' ', getInput('search')) as $search) {
                        $chart = $chart->where('name', 'like', '%' . $search . '%');
                    }
                }
                $chart = $chart->select(DB::raw('code_group as id'), DB::raw('name as text'))->get();
                $finalChart = $finalChart->merge($chart);
            }
            if ($kind == 'persediaan') {
                $chart = ChartAccountAlias::aktif()->child()->whereBetween('code_group', [140000, 150000]);
                if (getInput('search')) {
                    foreach (explode(' ', getInput('search')) as $search) {
                        $chart = $chart->where('name', 'like', '%' . $search . '%');
                    }
                }
                $chart = $chart->select(DB::raw('code_group as id'), DB::raw('name as text'))->get();
                $finalChart = $finalChart->merge($chart);
            }
            if ($kind == 'kartu-inventory') {
                $chart = ChartAccountAlias::aktif()->child()->where('reference_model', 'App\\Models\\KartuInventory');
                if (getInput('search')) {
                    foreach (explode(' ', getInput('search')) as $search) {
                        $chart = $chart->where('name', 'like', '%' . $search . '%');
                    }
                }
                $chart = $chart->select(DB::raw('code_group as id'), DB::raw('name as text'))->get();
                $finalChart = $finalChart->merge($chart);
            }
            if ($kind == 'inventory') {
                $chart = ChartAccountAlias::aktif()->child()->where(function ($q) {
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
                $chart = ChartAccountAlias::aktif()->child()->where(function ($q) {
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
                $chart = ChartAccountAlias::aktif()->child()->where(function ($q) {
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
                $chart = ChartAccountAlias::aktif()->child()->where(function ($q) {
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
                $chart = ChartAccountAlias::aktif()->child()->where('account_type', 'Beban');
                if (getInput('search')) {
                    foreach (explode(' ', getInput('search')) as $search) {
                        $chart = $chart->where('name', 'like', '%' . $search . '%');
                    }
                }
                $chart = $chart->select(DB::raw('code_group as id'), DB::raw('name as text'))->get();
                $finalChart = $finalChart->merge($chart);
            }
            if ($kind == 'pemindahan') {
                $chart = ChartAccountAlias::aktif()->child()->where('name', 'Ayat Silang');
                if (getInput('search')) {
                    foreach (explode(' ', getInput('search')) as $search) {
                        $chart = $chart->where('name', 'like', '%' . $search . '%');
                    }
                }
                $chart = $chart->select(DB::raw('code_group as id'), DB::raw('name as text'))->get();
                $finalChart = $finalChart->merge($chart);
            }
            if ($kind == 'modal') {
                $chart = ChartAccountAlias::aktif()->child()->where('account_type', 'Ekuitas');
                if (getInput('search')) {
                    foreach (explode(' ', getInput('search')) as $search) {
                        $chart = $chart->where('name', 'like', '%' . $search . '%');
                    }
                }
                $chart = $chart->select(DB::raw('code_group as id'), DB::raw('name as text'))->get();
                $finalChart = $finalChart->merge($chart);
            }
            if ($kind == 'lainlain') {
                $chart = ChartAccountAlias::aktif()->child();
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
        $chart = ChartAccountAlias::where('code_group', 181000)->first();
        $charts = ChartAccountAlias::where('parent_id', $chart->id);
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
        $chart = ChartAccountAlias::where('code_group', 160000)->first();
        $charts = ChartAccountAlias::where('parent_id', $chart->id);
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
        $chart = ChartAccountAlias::where('code_group', $id)->first();
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
        $chart = ChartAccountAlias::find($id);
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
    
        $referenceModel = $request->input('reference_model');
        if($request->input('no_update_ref')??false != true){
            $chart = ChartAccount::where('code_group', $codeGroup)->first();
            $chart->reference_model = $referenceModel;
            $chart->save();
        }
        $alias = ChartAccountAlias::createOrUpdate(new Request([
            'book_journal_id' => bookID(),
            'chart_account_id' => $chart->id,
            'code_group' => $codeGroup,
            'name' => $name,
            'reference_model' => $referenceModel,
        ]));
        return [
            'status' => 1,
            'msg' => $alias,
            'reference_model' => $referenceModel
        ];
    }

    public function updateAllLevel()
    {
        $chartAccounts = ChartAccountAlias::all();
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
        $chart = ChartAccountAlias::find($id);  
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
