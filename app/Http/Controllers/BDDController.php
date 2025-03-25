<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BDDController extends Controller
{
    public function DaftarBDD() {
        
        return view('daftar.daftar-bdd');
    }
}
