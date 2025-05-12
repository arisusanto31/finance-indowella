<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class DetailKartuInvoice extends Model
{
    //
    protected $table = 'detail_kartu_invoices';
    public $timestamps = true;
    protected $fillable = [
        'book_journal_id',
        'invoice_pack_id',
        'invoice_pack_number',
        'kartu_type',
        'kartu_id',
        'journal_id',
        'amount_journal',
        'sales_order_id',
        'sales_order_number',
        'purchase_order_id',
        'purchase_order_number',
    ];

    public function invoicePack()
    {
        return $this->belongsTo(InvoicePack::class, 'invoice_pack_id', 'id');
    }
    public function journal()
    {
        return $this->belongsTo(Journal::class, 'journal_id', 'id');
    }
    public function kartu()
    {
        return $this->morphTo();
    }

    public static function storeData(Request $request)
    {
        try {
            $kartuType = $request->input('kartu_type');
            $kartuId = $request->input('kartu_id');
            $invoicePackID = $request->input('invoice_pack_id');
            $saleOrderID = $request->input('sales_order_id');
            $purchaseOrderID = $request->input('purchase_order_id');

            $invoiceNumber = $request->input('invoice_pack_number');
            $salesOrderNumber = $request->input('sales_order_number');
            $purchaseOrderNumber = $request->input('purchase_order_number');

            if ($kartuType != null && $kartuId != null) {
                $kartu = $kartuType::find($kartuId);
                if (!$kartu) {
                    return ['status' => 0, 'msg' => 'Kartu not found'];
                }
                $journal = Journal::find($kartu->journal_id);
                if (!$journal) {
                    return ['status' => 0, 'msg' => 'Journal not found'];
                }
            } else {
                //harus ada journal nya berati
                $journal = Journal::find($request->input('journal_id'));
                if (!$journal) {
                    return ['status' => 0, 'msg' => 'Journal not found'];
                }
            }

            //oke dari sini sudah ada jurnal dan invoice pack, dan bisa jadi ada kartu
            $dt = DetailKartuInvoice::create([
                'kartu_type' => $kartuType,
                'kartu_id' => $kartuId,
                'book_journal_id' => session('book_journal_id'),
                'invoice_pack_id' => $invoicePackID,
                'invoice_pack_number' => $invoiceNumber,
                'sales_order_id' => $saleOrderID,
                'sales_order_number' => $salesOrderNumber,
                'purchase_order_id' => $purchaseOrderID,
                'purchase_order_number' => $purchaseOrderNumber,
                'journal_id' => $journal->id,
                'amount_journal' => $journal->amount_debet - $journal->amount_kredit,
            ]);

            return [
                'status' => 1,
                'msg' => $dt
            ];
        } catch (\Exception $e) {
            return [
                'status' => 0,
                'msg' => $e->getMessage()
            ];
        }
    }
}
