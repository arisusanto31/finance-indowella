<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KartuStockController extends Controller
{
    
    public function KartuStock(){
        return view('kartu.kartu-stock');
    }
}
