<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\InvoiceSaleDetail;
use App\Models\Customer;
use App\Traits\HasModelChilds;

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
        'invoice_date',
        'total_price',
        'status',
    ];


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
            return $val;
            
        })->groupBy('type_kartu');
        return $kartus;
    }
}
