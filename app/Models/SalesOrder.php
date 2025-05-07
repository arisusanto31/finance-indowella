<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    //
    protected $table = 'sales_orders';

    protected $fillable = [
        'book_journal_id',
        'sales_order_number',
        'toko_id',
        'customer_id',
        'total_price',
        'status',
        'reference_id',
        'reference_type',
        
    ];

    public function customer(){
        return $this->belongsTo(Customer::class, 'customer_id', 'id');  
    }
    public function stock(){
        return $this->belongsTo(Stock::class, 'stock_id', 'id');
    }
}
