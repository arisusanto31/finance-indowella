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
        $view = view('coa.index');

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
        $charts = $charts->select('id', DB::raw('concat(code_group,"-",name) as text'))->get();
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
        if ($kind == 'pendapatan') {
            $chart = ChartAccount::aktif()->child()->where('account_type', 'Pendapatan');
            if(getInput('search')){
                foreach(explode(' ',getInput('search')) as $search){
                    $chart = $chart->where('name','like', '%'. $search . '%');
                }
            }
            $chart= $chart->select(DB::raw('code_group as id'), DB::raw('name as text'))->get();

        } else if ($kind == 'beban') {
            $chart = ChartAccount::aktif()->child()->where('account_type', 'Beban');
            if(getInput('search')){
                foreach(explode(' ',getInput('search')) as $search){
                    $chart = $chart->where('name','like', '%'. $search . '%');
                }
            }
            $chart= $chart->select(DB::raw('code_group as id'), DB::raw('name as text'))->get();
        } else if ($kind == 'pemindahan') {
            $chart = ChartAccount::aktif()->child()->where('name', 'Ayat Silang');
            if(getInput('search')){
                foreach(explode(' ',getInput('search')) as $search){
                    $chart = $chart->where('name','like', '%'. $search . '%');
                }
            }
            $chart= $chart->select(DB::raw('code_group as id'), DB::raw('name as text'))->get();
        }
        else if($kind == 'modal'){
            $chart = ChartAccount::aktif()->child()->where('account_type', 'Ekuitas');
            if(getInput('search')){
                foreach(explode(' ',getInput('search')) as $search){
                    $chart = $chart->where('name','like', '%'. $search . '%');
                }
            }
            $chart= $chart->select(DB::raw('code_group as id'), DB::raw('name as text'))->get();
    
        } else if($kind == 'lainlain'){
            $chart = ChartAccount::aktif()->child();
            if(getInput('search')){
                foreach(explode(' ',getInput('search')) as $search){
                    $chart = $chart->where('name','like', '%'. $search . '%');
                }
            }
            $chart= $chart->select(DB::raw('code_group as id'), DB::raw('name as text'))->get();
    
        }
        return [
            'results' => $chart
        ];
    }

    public function getItemChartAccountAsetTetap(){
        $chart= ChartAccount::where('code_group',181000)->first();
        $charts = ChartAccount::where('parent_id',$chart->id);
        if(getInput('search')){
            foreach(explode(' ',getInput('search')) as $search){
                $charts = $charts->where('name','like', '%'. $search . '%');
            }
        }
        $charts= $charts->select(DB::raw('code_group as id'),DB::raw('name as text'))->get();
        return [
            'results'=>$charts
        ];
    }


    public function getItemChartAccountBDD(){
        $chart= ChartAccount::where('code_group',160000)->first();
        $charts = ChartAccount::where('parent_id',$chart->id);
        if(getInput('search')){
            foreach(explode(' ',getInput('search')) as $search){
                $charts = $charts->where('name','like', '%'. $search . '%');
            }
        }
        $charts= $charts->select(DB::raw('code_group as id'),DB::raw('name as text'))->get();
        return [
            'results'=>$charts
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

    public function destroy($id){
        $chart = ChartAccount::find($id);
        $chart->is_deleted=1;
        $chart->deleted_at= Date('Y-m-d H:i:s');
        $chart->save();
        return [
            'status'=>1,'msg'=>$chart
        ];
    }



}
