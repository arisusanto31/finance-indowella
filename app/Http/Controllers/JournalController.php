<?php

namespace App\Http\Controllers;

use App\Models\BookJournal;
use App\Models\ChartAccount;
use Illuminate\Http\Request;

class JournalController extends Controller
{
    //


    public function random()
    {
        $view = view('main.random');
        return $view;
    }

    public function neraca()
    {
        $view= view('main.neraca');

        // $starttime = microtime(true);
        // $date = getInput('date') ? getInput('date') : carbonDate();
        // $query = ChartAccount::getRincianNeracaAt($date);
        // if ($query['status'] == 0)
        //     return $query;
        // $chartAccounts = $query['msg'];
        // $laba = ChartAccount::getLabaBulanAt($date);
        // $aset = collect($chartAccounts['Aset'])->sum('saldo');
        // $kewajiban = collect($chartAccounts['Kewajiban'])->sum('saldo');
        // $ekuitas = collect($chartAccounts['Ekuitas'])->sum('saldo');
        // // return [
        // //     'status' => 1,
        // //     'time' => microtime(true) - $starttime,
        // //     'date' => $date,
        // //     'msg' => $chartAccounts,
        // //     'Aset' => $aset,
        // //     'Kewajiban' => $kewajiban,
        // //     'Ekuitas' => $ekuitas,
        // //     'laba_bulan' => $laba,
        // //     'balance' => $aset - ($kewajiban + $ekuitas + $laba)
        // // ];
        // $view->chartAccounts= $chartAccounts;
        // $view->date= $date;
        // $view->aset= $aset;
        // $view->kewajiban= $kewajiban;
        // $view->ekuitas= $ekuitas;
        // $view->laba= $laba;
        // $view->balance= $aset - ($kewajiban + $ekuitas + $laba);
        return $view;
    }

    public function neracalajur()
    {
        return view('main.neraca-lajur');
    }
    public function labarugi()
    {
        return view('main.laba-rugi');
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

    public function logoutJurnal(){
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
