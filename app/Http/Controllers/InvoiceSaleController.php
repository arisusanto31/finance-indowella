<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InvoiceSaleDetail;
use App\Models\Stock;
use App\Models\StockCategory;

class InvoiceSaleController extends Controller
{
    public function showSales()
    {
        $invoices = InvoiceSaleDetail::latest()->get();
        $categories = \App\Models\StockCategory::all();
        $stocks = Stock::all();
        $categories = StockCategory::all();
        $stocks = Stock::with(['category', 'parentCategory', 'units'])->get();


        return view('invoice.invoice-sales', compact('invoices','stocks','categories'));
    }
   
    public function store(Request $request)
    {
      
        $request->validate([
            'stock_id' => 'required|array',
            'stock_id.*' => 'required|integer',
            'quantity' => 'required|array',
            'quantity.*' => 'required|numeric',
            'price_unit' => 'required|array',
            'price_unit.*' => 'required|numeric',
            'unit' => 'required|array',
            'unit.*' => 'required|string',
        ]);

       
        $prefix = 'INV-' . now()->format('Ym');
        $last = InvoiceSaleDetail::where('invoice_number', 'like', "$prefix%")->count();
        $invoice_number = $prefix . '-' . str_pad($last + 1, 4, '0', STR_PAD_LEFT);

        
        foreach ($request->stock_id as $i => $stockId) {
            $qty = $request->quantity[$i];
            $price = $request->price_unit[$i];
            $discount = $request->discount[$i] ?? 0;
            $total = ($qty * $price) - $discount;

            InvoiceSaleDetail::create([
                'invoice_number' => $invoice_number,
                'stock_id' => $stockId,
                'quantity' => $qty,
                'unit' => $request->unit[$i],
                'price' => $price,
                'total_price' => $total,
                'discount' => $discount,
                'customer_id' => $request->customer_id ?? null,
            ]);
        }

        return redirect()->route('invoice-sale.index')->with('success', 'Invoice berhasil disimpan!');
    }
   

}
