<?php

namespace App\Http\Controllers;

use App\Models\InvoicePack;

class InvoicePackController extends Controller

{

    public function show($id)
{
    $invoice = \App\Models\InvoicePack::find($id);

    if (!$invoice) {
        dd('Invoice tidak ditemukan');
    }

    return view('invoice.show', compact('invoice'));
}
}

//     public function show($id)


//     // {
//     //     $invoice = InvoicePack::with('invoiceDetails')->findOrFail($id);
//     //     return view('invoice.show', compact('invoice'));
//     // }

// }
