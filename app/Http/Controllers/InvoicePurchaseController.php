<?php

namespace App\Http\Controllers;

use App\Models\InvoicePack;
use App\Models\InvoicePurchaseDetail;
use App\Models\Stock;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;
use App\Models\InvoicePurchase;
use Dotenv\Exception\ValidationException;

class InvoicePurchaseController extends Controller
{


    public function showPurchase()
    {
        $invoices = InvoicePurchaseDetail::with(['parent', 'stock', 'supplier'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('invoice_pack_number');

        return view('invoice.invoice-purchase', compact('invoices'));
    }



    public function editInvoicePurchase($invoiceNumber)
    {



        $data = InvoicePack::where('invoice_number', $invoiceNumber)->first();

        $details = InvoicePurchaseDetail::with('stock')
            ->where('invoice_pack_id', $data->id)
            ->get();


        foreach ($details as $detail) {
            $detail->total_price = ($detail->quantity * $detail->price) - $detail->discount;
        }

        $data['details'] = $details;

        $data['total_price'] = $details->sum(fn($item) => $item->total_price);

        $view = view('invoice.modal._edit-purchase', [
            'invoiceNumber' => $invoiceNumber,
            'data' => $data,
        ]);
        $view->invoiceNumber = $invoiceNumber;
        $view->data = $data;

        return $view;
    }

    public function updateInvoicePurchase(Request $request)
    {

        try {

            DB::beginTransaction();
            $invoiceNumber = $request->input('original_invoice_number');
            $newInvoiceNumber = $request->input('new_invoice_number');
            $invoicePack = InvoicePack::where('invoice_number', $invoiceNumber)->first();
            $detailIDs = $request->input('detail_id');
            $date = $request->input('date');
            $allInv = [];
            foreach ($detailIDs as $i => $detailId) {
                $invDetail = InvoicePurchaseDetail::find($detailId);
                if (!$invDetail) {
                    throw new ValidationException("Detail with ID {$detailId} not found.");
                }
                $data = [
                    'invoice_pack_number' => $newInvoiceNumber,
                    'quantity' => format_db($request->input('quantity')[$i]),
                    'price' => format_db($request->input('price')[$i]),
                    'discount' => format_db($request->input('discount')[$i]) ?? 0,
                    'total_price' => format_db($request->input('total_price')[$i]) ?? 0,
                    'custom_stock_name' => $request->input('custom_stock_name')[$i] ?? $invDetail->stock->name,
                    'created_at' => $date
                ];
                $invDetail->update($data);
                $allInv[] = $invDetail;
            }
            $invoicePack->update([
                'invoice_number' => $newInvoiceNumber,
                'total_price' => collect($allInv)->sum('total_price'),
                'created_at' => $date
            ]);
            DB::commit();
            return ['status' => 1, 'msg' => $invoicePack, 'details' => $allInv];
        } catch (ValidationException $e) {
            DB::rollBack();
            return ['status' => 0, 'msg' => getErrorValidation($e)];
        } catch (Throwable $e) {
            DB::rollBack();
            return ['status' => 0, 'msg' => $e->getMessage(), 'trace' => $e->getTrace()];
        }
    }

    public function createMutations(Request $request)
    {
        return $request->all();
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
        $date = $request->input('date');
        foreach ($request->stock_id as $i => $stockId) {
            $thestock = Stock::find($stockId);
            $grouped[] = [
                'invoice_pack_number' => $invoice_pack_number,
                'stock_id' => $stockId,
                'quantity' => $request->quantity[$i],
                'unit' => $request->unit[$i],
                'price' => $request->price_unit[$i],
                'discount' => $request->discount[$i] ?? 0,
                'supplier_id' => $request->supplier_id,
                'book_journal_id' => bookID(),
                'total_price' => format_db($request->total_price[$i]) ?? 0,
                'custom_stock_name' => $request->custom_stock_name[$i] ?? $thestock->name,
                'created_at' => $date ?? now()
            ];
        }

        DB::beginTransaction();
        try {

            //create pack ya
            $invoicePack = InvoicePack::create([

                'invoice_number' => $invoice_pack_number,
                'book_journal_id' => bookID(),
                'person_id' => $request->supplier_id,
                'person_type' => 'App\Models\Supplier',
                'reference_model' => 'App\Models\InvoicePurchaseDetail',
                'invoice_date' => now(),
                'total_price' => collect($grouped)->sum('total_price'),
                'status' => 'draft',
                'created_at' => $date ?? now()
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
