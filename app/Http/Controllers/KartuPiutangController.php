<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KartuPiutangController extends Controller
{
    
    public function KartuPiutang(){
        return view('kartu.kartu-piutang');
    }
}
