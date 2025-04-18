<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InvoiceSaleDetail;
use App\Models\Stock;
use App\Models\StockCategory;
use App\Models\InvoicePack;

class InvoiceSaleController extends Controller
{

    protected $fillable = [
        'invoice_number',
        'stock_id',
        'quantity',
        'unit',
        'price',
        'total_price',
        'discount',
        'customer_id',
    ];
    

    public function stock()
    {
        return $this->belongsTo(\App\Models\Stock::class, 'stock_id');
    }

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class, 'customer_id');
    }
        

    public function showSales()
    {
        $invoices = \App\Models\InvoiceSaleDetail::with('stock')
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
                    'customer_id' => $request->customer_id,
                ];
            } else {
                $grouped[$key]['quantity'] += $request->quantity[$i];
                $grouped[$key]['discount'] += $request->discount[$i] ?? 0;
            }
        }
    
        foreach ($grouped as &$data) {
            $data['total_price'] = ($data['quantity'] * $data['price']) - $data['discount'];
            InvoiceSaleDetail::create($data);
        }
    
        
        return redirect()->route('invoice.sales.index')->with('success', 'Invoice berhasil disimpan luurr 😃!');
    }

    public function getItem()
    {
        $searchs = explode(' ', request('search'));
        $cust = Customer::query();

        foreach ($searchs as $s) {
            $cust->where('name', 'like', "%$s%");
        }

        $cust = $cust->select('id', DB::raw('name as text'))->get();

        return ['results' => $cust];
    }
} 

