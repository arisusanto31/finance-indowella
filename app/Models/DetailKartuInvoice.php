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
        'invoice_number',
        'kartu_type',
        'kartu_id',
        'journal_id',
        'amount_journal'
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
        $kartuType = $request->input('kartu_type');
        $kartuId = $request->input('kartu_id');
        $invoicePackID = $request->input('invoice_pack_id');

        $invoicePack = InvoicePack::find($invoicePackID);
        if (!$invoicePack) {
            return ['status' => 0, 'msg' => 'Invoice Pack not found'];
        }
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
            'invoice_pack_id' => $invoicePackID,
            'book_journal_id' => $invoicePack->book_journal_id,
            'invoice_number' => $invoicePack->invoice_number,
            'journal_id' => $journal->id,
            'amount_journal' => $journal->amount_debet - $journal->amount_kredit,
        ]);

        return [
            'status' => 1,
            'msg' => $dt
        ];
    }
}
