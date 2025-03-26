<?php

namespace App\Http\Controllers;

use App\Models\BookJournal;
use Illuminate\Http\Request;

class JournalController extends Controller
{
    //


    public function neraca()
    {
        return view('main.neraca');
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
