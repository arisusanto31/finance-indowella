<?php

namespace App\Models;

use App\Traits\HasModelDetailKartuInvoice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class InvoiceSaleDetail extends Model
{

    use HasModelDetailKartuInvoice;
    protected $fillable = [
        'invoice_pack_number',
        'invoice_pack_id',
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

        static::updating(function ($model) {});
    }

    public function parent()
    {

        return $this->belongsTo(InvoicePack::class, 'invoice_pack_number', 'invoice_number');
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
