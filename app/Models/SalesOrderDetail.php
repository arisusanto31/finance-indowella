<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SalesOrderDetail extends Model
{
    //

    protected $table = 'sales_order_details';
    public $timestamps = true;
    protected $fillable = [
        'sales_order_number',
        'sales_order_id',
        'book_journal_id',
        'stock_id',
        'custom_stock_name',
        'quantity',
        'unit',
        'price',
        'total_price',
        'discount',
        'customer_id',
        'toko_id',
        'reference_id',
        'reference_type'
    ];

    public function parent(){
        return $this->belongsTo(SalesOrder::class, 'sales_order_number', 'sales_order_number');
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
    public function stock()
    {
        return $this->belongsTo(Stock::class, 'stock_id', 'id');
    }

    protected static function booted()
    {
        static::addGlobalScope('journal', function ($query) {
            $from = $query->getQuery()->from ?? 'sales_order_details'; // untuk dukung alias `j` kalau pakai from('journals as j')
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
}
