<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Models\InvoiceSaleDetail;
use App\Models\Stock;
use App\Models\StockCategory;
use App\Models\InvoicePack;
use Illuminate\Support\Facades\DB;
use Throwable;

class InvoiceSaleController extends Controller
{


    public function showSales()
    {
        $invoices = \App\Models\InvoiceSaleDetail::with('customer', 'stock')
            ->get()
            ->groupBy('invoice_number');

        // dd($invoices);

        return view('invoice.invoice-sales', compact('invoices'));
    }



    // public function showSales()
    // {
    //     $invoices = InvoiceSaleDetail::latest()->get();
    //     $categories = \App\Models\StockCategory::all();
    //     $stocks = Stock::all();
    //     $categories = StockCategory::all();
    //     $stocks = Stock::with(['category', 'parentCategory', 'units'])->get();


    //     return view('invoice.invoice-sales', compact('invoices','stocks','categories'));
    // }

    public function store(Request $request)
    {


        $request->validate([
            'invoice_number' => 'required|string|max:255',
            'customer_id' => 'required|integer',
            'stock_id' => 'required|array',
            'stock_id.*' => 'required|integer',
            'quantity' => 'required|array',
            'quantity.*' => 'required|numeric',
            'price_unit' => 'required|array',
            'price_unit.*' => 'required|numeric',
            'unit' => 'required|array',
            'unit.*' => 'required|string',
            'total_price' => 'required|array',
            'total_price.*' => 'required|string',
            'toko_id'=>'required|integer',
        ]);

        $invoice_number = $request->invoice_number;
        $grouped = [];

        foreach ($request->stock_id as $i => $stockId) {
        

            $grouped[] = [
                'invoice_number' => $invoice_number,
                'stock_id' => $stockId,
                'quantity' => $request->quantity[$i],
                'unit' => $request->unit[$i],
                'price' => $request->price_unit[$i],
                'discount' => $request->discount[$i] ?? 0,
                'customer_id' => $request->customer_id,
                'book_journal_id' => session('book_journal_id'),
                'total_price' => format_db($request->total_price[$i]) ?? 0,
                'toko_id'=>$request->toko_id,
            ];
        }

        DB::beginTransaction();
        try {
            //create pack ya
            $invoicePack = InvoicePack::create([
                'invoice_number' => $invoice_number,
                'book_journal_id' => session('book_journal_id'),
                'person_id' => $request->customer_id,
                'person_type' => Customer::class,
                'reference_model' => InvoiceSaleDetail::class,
                'invoice_date' => now(),
                'total_price' => collect($grouped)->sum('total_price'),
                'status' => 'draft',
                'toko_id'=>$request->toko_id,
            ]);

            foreach ($grouped as $data) {
                $data['invoice_pack_id'] = $invoicePack->id;
                InvoiceSaleDetail::create($data);
            }

            DB::commit();
        } catch (Throwable $e) {

            DB::rollBack();
            return ['status' => 0, 'msg' => $e->getMessage()];
        }

        return ['status' => 1, 'msg' => 'Data berhasil disimpan'];
    }
}
