<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class InvoiceSaleDetail extends Model
{
    protected $fillable = [
        'invoice_number',
        'book_journal_id',
        'stock_id',
        'quantity',
        'unit',
        'price',
        'total_price',
        'discount',
        'customer_id',
    ];

    protected static function booted()
    {
        static::addGlobalScope('journal', function ($query) {
            $from = $query->getQuery()->from ?? 'invoice_sale_details'; // untuk dukung alias `j` kalau pakai from('journals as j')
            if (Str::contains($from, ' as ')) {
                [$table, $alias] = explode(' as ', $from);
                $alias = trim($alias);
            } else {
                $alias = $from;
            }
        
            $query->where(function ($q) use ($alias) {
                $q->whereNull("{$alias}.book_journal_id")
                    ->orWhere("{$alias}.book_journal_id", session('book_journal_id'));
            });
        });
    }

public function stock()

{
    return $this->belongsTo(\App\Models\Stock::class, 'stock_id', 'id');
}

public function customer()
{
return $this->belongsTo(\App\Models\Customer::class, 'customer_id');
}



}
