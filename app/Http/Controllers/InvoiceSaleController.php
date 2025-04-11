<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InvoiceSaleController extends Controller
{
    public function ShowSales()
    {
        return view('invoice.invoice-sales');
    }
}
