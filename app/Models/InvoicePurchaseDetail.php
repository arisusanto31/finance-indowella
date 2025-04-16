<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoicePurchaseDetail extends Model
{
    public function invoicePack()
{
    return $this->belongsTo(InvoicePack::class, 'invoice_number', 'invoice_number');
}

}
