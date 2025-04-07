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

    public function createMutasiMasuk(){
        $view = view('kartu.modal._kartu-stock-masuk');
        return $view;
    }
    public function createMutasiKeluar() {

    }
    public function getMutasiMasuk() {}
    public function getMutasiKeluar() {}
}
