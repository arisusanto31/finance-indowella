<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KartuKasController extends Controller
{
    
    public function KartuKAs(){
        return view('kartu.kartu-kas');
    }
}
