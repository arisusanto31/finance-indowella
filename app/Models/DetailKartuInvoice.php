<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class DetailKartuInvoice extends Model
{
    //model ini adalah link antara jurnal - kartu - invoice


    protected $table = 'detail_kartu_invoices';
    public $timestamps = true;
    protected $fillable = [
        'book_journal_id',
        'invoice_pack_id',
        'invoice_pack_number',
        'kartu_type',
        'kartu_id',
        'journal_id',
        'journal_number',
        'account_code_group',
        'account_name',
        'amount_journal',
        'amount_debet',
        'amount_kredit',
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

            $journal = Journal::find($request->input('journal_id'));
            if (!$journal && $request->input('journal_id') != null) {
                return ['status' => 0, 'msg' => 'jurnal tidak ditemukan'];
            }
            if ($kartuType == Journal::class) {
                $kartuType = null;
                $kartuId = null;
            }
            if ($kartuType != null && $kartuId != null) {
                $kartu = $kartuType::find($kartuId);
                if (!$kartu) {
                    return ['status' => 0, 'msg' => 'Kartu not found'];
                }
            }


            $dataUpdate = [
                'kartu_type' => $kartuType,
                'kartu_id' => $kartuId,
                'book_journal_id' => bookID(),
                'invoice_pack_id' => $invoicePackID,
                'invoice_pack_number' => $invoiceNumber,
                'sales_order_id' => $saleOrderID,
                'sales_order_number' => $salesOrderNumber,
                'purchase_order_id' => $purchaseOrderID,
                'purchase_order_number' => $purchaseOrderNumber,
                'journal_id' => $journal?$journal->id:null,
                'journal_number' => $journal?$journal->journal_number:null,
                'account_code_group' => $journal?$journal->code_group:null,
                'account_name' => $journal && $journal->chartAccountAlias ? $journal->chartAccountAlias->name : '',
                'amount_journal' => $journal ? $journal->amount_debet - $journal->amount_kredit : 0,
                'amount_debet' => $journal ? $journal->amount_debet : 0,
                'amount_kredit' => $journal ? $journal->amount_kredit : 0,
            ];

            //oke dari sini sudah ada jurnal dan invoice pack, dan bisa jadi ada kartu
            $dt = null;

            $dt = DetailKartuInvoice::where('journal_id', $journal->id)
                ->first();
            if ($dt) {
                $dt->updateData($dataUpdate);
            } else {
                //coba cari dari kartu nya ..
                if ($kartuType != null && $kartuId != null) {
                    $dt = DetailKartuInvoice::where('kartu_type', $kartuType)->where('kartu_id', $kartuId)
                        ->first();
                    if ($dt) {
                        $dt->updateData($dataUpdate);
                    } else {
                        $dt = DetailKartuInvoice::create($dataUpdate);
                    }
                }else{
                    $dt = DetailKartuInvoice::create($dataUpdate);
                }
            }

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

    public function updateData($data)
    {

        //hanya update yang ada datanya saja.
        if ($data['kartu_type']) {
            $this->kartu_type = $data['kartu_type'];
        }
        if ($data['kartu_id']) {
            $this->kartu_id = $data['kartu_id'];
        }
        if ($data['invoice_pack_id']) {
            $this->invoice_pack_id = $data['invoice_pack_id'];
        }
        if ($data['invoice_pack_number']) {
            $this->invoice_pack_number = $data['invoice_pack_number'];
        }
        if ($data['sales_order_id']) {
            $this->sales_order_id = $data['sales_order_id'];
        }
        if ($data['sales_order_number']) {
            $this->sales_order_number = $data['sales_order_number'];
        }
        if ($data['purchase_order_id']) {
            $this->purchase_order_id = $data['purchase_order_id'];
        }
        if ($data['purchase_order_number']) {
            $this->purchase_order_number = $data['purchase_order_number'];
        }
        if ($data['journal_id']) {
            $this->journal_id = $data['journal_id'];
        }
        if ($data['journal_number']) {
            $this->journal_number = $data['journal_number'];
        }
        if ($data['account_code_group']) {
            $this->account_code_group = $data['account_code_group'];
        }
        if ($data['account_name']) {
            $this->account_name = $data['account_name'];
        }
        if ($data['amount_journal']) {
            $this->amount_journal = $data['amount_journal'];
        }
        $this->save();
        return $this;
    }
}
