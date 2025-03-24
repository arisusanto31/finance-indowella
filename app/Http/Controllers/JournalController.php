<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class JournalController extends Controller
{
    //


    public function neraca(){
        return view('main.neraca');
    }

    public function neracalajur(){
        return view('main.neraca-lajur');
    }
    public function labarugi(){
        return view('main.laba-rugi');
    }
    public function jurnal(){
        return view('main.jurnal');
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
