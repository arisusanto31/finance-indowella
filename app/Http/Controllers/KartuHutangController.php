<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KartuHutangController extends Controller
{
    
    public function KartuHutang(){
        return view('kartu.kartu-hutang');
    }
}
