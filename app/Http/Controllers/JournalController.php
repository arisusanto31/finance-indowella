<?php

namespace App\Http\Controllers;

use App\Models\BookJournal;
use App\Models\ChartAccount;
use Illuminate\Http\Request;

class JournalController extends Controller
{
    //




    public function neraca()
    {
        $view = view('main.neraca');

        $starttime = microtime(true);
        $date = getInput('date') ? getInput('date') : carbonDate();
        $query = ChartAccount::getRincianNeracaAt($date);
        if ($query['status'] == 0)
            return $query;
        $chartAccounts = $query['msg'];
        $laba = ChartAccount::getLabaBulanAt($date);
        $aset = collect($chartAccounts['Aset'])->sum('saldo');
        $kewajiban = collect($chartAccounts['Kewajiban'])->sum('saldo');
        $ekuitas = collect($chartAccounts['Ekuitas'])->sum('saldo');
        $jsdata = [
            'status' => 1,
            'time' => microtime(true) - $starttime,
            'date' => $date,
            'msg' => $chartAccounts,
            'Aset' => $aset,
            'Kewajiban' => $kewajiban,
            'Ekuitas' => $ekuitas,
            'laba_bulan' => $laba,
            'balance' => $aset - ($kewajiban + $ekuitas + $laba)
        ];
        $view->jsdata = $jsdata;

        return $view;
    }

    public function neracalajur()
    {
        $view = view('main.neraca-lajur');
        $month = getInput('month') ? getInput('month') : Date('m');
        $year = getInput('year') ? createCarbon(getInput('year') . '-01-01')->format('y') : Date('y');
        $view->data =  ChartAccount::getRincianSaldoNeracaLajur($month, $year);
        return $view;
    }
    public function getMutasiNeracaLajur()
    {
        $month = getInput('month') ? getInput('month') : Date('m');
        $year = getInput('year') ? createCarbon(getInput('year') . '-01-01')->format('y') : Date('y');
        return ChartAccount::getRincianMutationNeracaLajur($month, $year);
    }
   
       

    public function labarugi()
    {
        $view= view('main.laba-rugi');
        $date =  getInput('date') ? getInput('date') : carbonDate();
        $labarugi = ChartAccount::getRincianLabaBulanAt($date);
        $data= [
            'status' => 1,
            'msg' => $labarugi,
            'laba_bulan' => round(collect($labarugi)->where('is_child', 1)->sum('saldo_akhir'), 2)
        ];
        $view->data= $data;
        return $view;
    }
    public function jurnal()
    {
        return view('main.jurnal');
    }
    public function pilihJurnal()
    {

        $view = view('main.pilih-jurnal');
        $view->books = BookJournal::get();
        return $view;
    }

    public function loginJurnal($id)
    {
        try {
            session()->put('book_journal_id', $id);
            return [
                'status' => 1,
                'msg' => 'success'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 0,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function logoutJurnal()
    {
        session()->forget('book_journal_id');
        return redirect()->route('pilih.jurnal');
    }


    public function bukuBesar()
    {
        return view('main.buku-besar');
    }

    public function mutasi()
    {
        return view('main.mutasi');
    }
}
