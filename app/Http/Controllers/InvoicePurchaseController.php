<?php

namespace App\Http\Controllers;

use App\Models\InvoicePurchaseDetail;
use Illuminate\Http\Request;

class InvoicePurchaseController extends Controller
{

    protected $table='invoice_purchase_details';
    public function ShowPurchase()
    {
        $invoices = \App\Models\InvoicePurchaseDetail::with('supplier', 'stock')
            ->get()
            ->groupBy('invoice_number');

        // dd($invoices);

        return view('invoice.invoice-purchase', compact('invoices'));
    }


    public function store(Request $request)
    {


        $request->validate([
            'invoice_number' => 'required|string|max:255',
            'supplier_id' => 'required|integer',
            'stock_id' => 'required|array',
            'stock_id.*' => 'required|integer',
            'quantity' => 'required|array',
            'quantity.*' => 'required|numeric',
            'price_unit' => 'required|array',
            'price_unit.*' => 'required|numeric',
            'unit' => 'required|array',
            'unit.*' => 'required|string',
        ]);

        $invoice_number = $request->invoice_number;
        $grouped = [];

        foreach ($request->stock_id as $i => $stockId) {
            $key = $invoice_number . '-' . $stockId;

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'invoice_number' => $invoice_number,
                    'stock_id' => $stockId,
                    'quantity' => $request->quantity[$i],
                    'unit' => $request->unit[$i],
                    'price' => $request->price_unit[$i],
                    'discount' => $request->discount[$i] ?? 0,
                    'supplier_id' => $request->supplier_id,
                    'book_journal_id' => session('book_journal_id'),
                ];
            } else {
                $grouped[$key]['quantity'] += $request->quantity[$i];
                $grouped[$key]['discount'] += $request->discount[$i] ?? 0;
            }
        }

        foreach ($grouped as &$data) {
            $data['total_price'] = ($data['quantity'] * $data['price']) - $data['discount'];
            InvoicePurchaseDetail::create($data);
        }

        return ['status' => 1, 'msg' => 'Data berhasil disimpan'];
    }
}
