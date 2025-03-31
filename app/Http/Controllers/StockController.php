<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StockController extends Controller
{
    public function Stock()
    {
        return view('master.stock');
    }
}
