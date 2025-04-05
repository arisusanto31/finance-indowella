<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KartuStockController extends Controller
{

    public function index()
    {
        return view('kartu.kartu-stock');
    }

    public function getSummary() {}

    public function getMutasiMasuk() {}
    public function getMutasiKeluar() {}
}
