<?php

namespace App\Http\Controllers;

use App\Models\InvoicePack;
use App\Models\InvoicePurchaseDetail;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class InvoicePurchaseController extends Controller
{

    protected $table = 'invoice_purchase_details';
    public function ShowPurchase()
    {
        $invoices = \App\Models\InvoicePurchaseDetail::with('supplier', 'stock')
            ->get()
            ->groupBy('invoice_pack_number');

        // dd($invoices);

        return view('invoice.invoice-purchase', compact('invoices'));
    }


    public function store(Request $request)
    {

      
        $request->validate([
            'invoice_pack_number' => 'required|string|max:255',
            'supplier_id' => 'required|integer',
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
            'discount' => 'nullable|array',
            'discount.*' => 'nullable|numeric',
        ]);

        $invoice_pack_number = $request->invoice_pack_number;
        $grouped = [];

        foreach ($request->stock_id as $i => $stockId) {

            $grouped[] = [
                'invoice_pack_number' => $invoice_pack_number,
                'stock_id' => $stockId,
                'quantity' => $request->quantity[$i],
                'unit' => $request->unit[$i],
                'price' => $request->price_unit[$i],
                'discount' => $request->discount[$i] ?? 0,
                'supplier_id' => $request->supplier_id,
                'book_journal_id' => session('book_journal_id'),
                'total_price' => format_db($request->total_price[$i]) ?? 0,
            ];
        }

        DB::beginTransaction();
        try {

            //create pack ya
            $invoicePack = InvoicePack::create([
                'invoice_number' => $invoice_pack_number,
                'book_journal_id' => session('book_journal_id'),
                'person_id' => $request->supplier_id,
                'person_type' => 'App\Models\Supplier',
                'reference_model' => 'App\Models\InvoicePurchaseDetail',
                'invoice_date' => now(),
                'total_price' => collect($grouped)->sum('total_price'),
                'status' => 'draft',
            ]);


            foreach ($grouped as $data) {
                $data['invoice_pack_id'] = $invoicePack->id;
                InvoicePurchaseDetail::create($data);
            }
            DB::commit();
        } catch (Throwable $e) {

            DB::rollBack();
            return ['status' => 0, 'msg' => $e->getMessage(), 'trace' => $e->getTrace()];
        }

        return ['status' => 1, 'msg' => 'Data berhasil disimpan'];
    }
}
