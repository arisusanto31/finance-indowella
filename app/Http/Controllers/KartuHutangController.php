<?php

namespace App\Http\Controllers;

use App\Models\KartuHutang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KartuHutangController extends Controller
{
    
    public function index(){

        $view= view('kartu.kartu-hutang');
        return $view;
    }

    public function createMutationHutang(Request $request){
        return KartuHutang::createMutation($request);
    }

    public function getSummaryKartuHutang(){
        $month= getInput('month')??Date('m');
        $year= getInput('year')??Date('Y');
        $date=$year.'-'.$month;
        $kartuHutang= KartuHutang::whereIn('id',function($q){
            $q->from('kartu_hutangs')->select(DB::raw('max(id)'))->groupBy('factur_supplier_number');
        })->where(function($q) use($date){
           
        });
    }
}
