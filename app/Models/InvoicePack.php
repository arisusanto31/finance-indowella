<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\InvoiceSaleDetail;
use App\Models\Customer;
use App\Traits\HasModelChilds;
use Illuminate\Support\Str;


class InvoicePack extends Model
{
    use HasModelChilds;
    protected $table = 'invoice_packs';
    protected $fillable = [
        'invoice_number',
        'book_journal_id',
        'person_id',
        'person_type',
        'reference_model',
        'sales_order_id',
        'invoice_date',
        'total_price',
        'status',
        'toko_id',
        'reference_id',
        'reference_type',
        'created_at',
    ];

    protected static function booted()
    {
        static::addGlobalScope('journal', function ($query) {
            $from = $query->getQuery()->from ?? 'invoice_packs'; // untuk dukung alias `j` kalau pakai from('journals as j')
            if (Str::contains($from, ' as ')) {
                [$table, $alias] = explode(' as ', $from);
                $alias = trim($alias);
            } else {
                $alias = $from;
            }

            $query->where(function ($q) use ($alias) {
                $q->whereNull("{$alias}.book_journal_id")
                    ->orWhere("{$alias}.book_journal_id", bookID());
            });
        });
    }

    public static function cekAvail($code, $count)
    {
        $invoice = InvoicePack::where('invoice_number', $code . $count)->first();
        if ($invoice) {
            return ['status' => 0, 'msg' => 'Invoice number already exists'];
        }
        return ['status' => 1, 'msg' => 'Invoice number available'];
    }
    public function invoiceDetails()
    {
        return $this->getChilds('invoice_pack_id');
    }


    public function person()
    {
        return $this->morphTo();
    }

    public function detailKartuInvoices()
    {

        return $this->hasMany(DetailKartuInvoice::class, 'invoice_pack_id', 'id');
    }

    public function getAllKartu()
    {
        $kartus = collect($this->detailKartuInvoices)->map(function ($val) {
            $val['type_kartu'] = $val->kartu_type ? explode('\\', $val->kartu_type)[2] : 'Kartu lain-lain';
            $val['code_group_name'] = $val->journal->chartAccount->name;
            $val['date'] = $val->journal->created_at->format('Y-m-d H:i:s');

            return $val;
        })->groupBy('type_kartu');
        return $kartus ?? [];
    }

    public function getCodeFix()
    {
        $data = $this;
        $personType = $data->person_type;
        $personID = $data->person_id;
        $inv = InvoicePack::where('is_final', 1)->where('person_id', $personID)
            ->where('person_type', $personType)
            ->orderBy('index', 'desc')->first();
        if ($inv) {
            $count = $inv->index + 1;
        } else {
            $count = 1;
        }
        $this->index = $count;
        if ($data->reference_model == InvoiceSaleDetail::class)
            $code = 'INV-S' . date('y') . '-' . toDigit($personID, 4) . '-' . toDigit($count, 4);
        else if ($data->reference_model == InvoicePurchaseDetail::class)
            $code = 'INV-P' . date('y') . '-' . toDigit($personID, 4) . '-' . toDigit($count, 4);
        else
            $code = 'INV-' . date('y') . '-' . toDigit($personID, 4) . '-' . toDigit($count, 4);
        return $code;
    }

    public function updateStatus()
    {
        $this->status = $this->is_final == 1 ? 'FINAL' : 'DRAFT';
        $this->save();
    }
}
