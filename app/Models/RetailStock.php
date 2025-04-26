<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RetailStock extends Model
{
    //
    protected $connection = "tokoSql";
    protected $table = "stocks";
    public $timestamps = true;


    protected static function booted()
    {
        static::addGlobalScope('aktif', function ($query) {
            $from = $query->getQuery()->from ?? 'stocks'; // untuk dukung alias `j` kalau pakai from('journals as j')
            if (Str::contains($from, ' as ')) {
                [$table, $alias] = explode(' as ', $from);
                $alias = trim($alias);
            } else {
                $alias = $from;
            }

            $query->where(function ($q) use ($alias) {
                $q->whereNull("{$alias}.deleted")->orWhere("{$alias}.deleted", 0);
            });
        });
    }
    public function category()
    {
        return $this->belongsTo(RetailStockCategory::class, 'category_id', 'id');
    }
    public function parentCategory()
    {
        return $this->belongsTo(RetailStockCategory::class, 'parent_category_id', 'id');
    }

    public static function withUnits($stocks)
    {
        // Ambil semua RetailStock

        if ($stocks->isEmpty()) {
            return collect(); // Kalau kosong, langsung kembalikan collection kosong
        }

        // Ambil semua RetailStock ID
        $stockIds = $stocks->pluck('id')->toArray();

        // Ambil semua ManufUnit yang berkaitan dari database manuf
        $units = ManufUnit::whereIn('retail_stock_id', $stockIds)
            ->where('is_dagang', 1)
            ->whereNull('is_disabled')
            ->select('unit', 'konversi', 'retail_stock_id')
            ->get()
            ->groupBy('retail_stock_id');

        // Pasangkan units ke masing-masing RetailStock
        foreach ($stocks as $stock) {
            $stock->units_manual = $units[$stock->id] ?? collect();
        }

        return $stocks;
    }
}
