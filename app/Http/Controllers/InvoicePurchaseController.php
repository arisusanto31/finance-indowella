<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InvoicePurchaseController extends Controller
{
    public function ShowPurchase()
    {
        return view('invoice.invoice-purchase');
    }
}
