<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceSaleDetail extends Model
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
    
}
