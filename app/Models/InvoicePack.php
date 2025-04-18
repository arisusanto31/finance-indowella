<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\InvoiceSaleDetail;
use App\Models\Customer;

class InvoicePack extends Model
{
    protected $fillable = [
        'invoice_number',
        'customer_id',
        'invoice_date',
        'total_price',
        'status',
        'bukti_file',
    ];

    
    public function invoiceDetails()
    {
        return $this->hasMany(InvoiceSaleDetail::class, 'invoice_number', 'invoice_number');
    }

   
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
