<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class InvoicePurchaseDetail extends Model
{

    protected $fillable = [
        'invoice_pack_number',
        'invoice_pack_id',
        'book_journal_id',
        'stock_id',
        'quantity',
        'unit',
        'price',
        'total_price',
        'discount',
        'supplier_id',
        'custom_stock_name',
        'created_at',
        'is_ppn',
        'total_ppn_m',

    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->custom_stock_name) {
                $stock = Stock::find($model->stock_id);
                if ($stock->name != 'custom') {
                    $model->custom_stock_name = $stock->name;
                }
            }
        });

        static::updating(function ($model) {
            if (!$model->custom_stock_name) {
                $stock = Stock::find($model->stock_id);
                if ($stock->name != 'custom') {
                    $model->custom_stock_name = $stock->name;
                }
            }
        });
    }
    protected static function booted()
    {
        static::addGlobalScope('journal', function ($query) {
            $from = $query->getQuery()->from ?? 'invoice_purchase_details'; // untuk dukung alias `j` kalau pakai from('journals as j')
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

    public function parent()
    {
        return $this->belongsTo(InvoicePack::class, 'invoice_pack_number', 'invoice_number');
    }
    public function invoicePack()
    {
        return $this->belongsTo(InvoicePack::class, 'invoice_pack_number', 'invoice_number');
    }


    public function stock()

    {
        return $this->belongsTo(\App\Models\Stock::class, 'stock_id', 'id');
    }

    public function supplier()
    {
        return $this->belongsTo(\App\Models\Supplier::class, 'supplier_id');
    }
}
