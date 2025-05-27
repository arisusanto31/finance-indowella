<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ManufStock extends Model
{
    //

    protected $connection = "manufSql";
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
    public function units()
    {
        return $this->hasMany(ManufUnit::class, 'stock_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo(ManufStockCategory::class, 'category_id', 'id');
    }
    public function parentCategory()
    {
        return $this->belongsTo(ManufStockCategory::class, 'parent_category_id', 'id');
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
        $units = ManufUnit::whereIn('stock_id', $stockIds)
            // ->where('is_dagang', 1)
            ->whereNull('is_disabled')
            ->select('unit', 'konversi', 'stock_id')
            ->get()
            ->groupBy('stock_id');

        // Pasangkan units ke masing-masing RetailStock
        foreach ($stocks as $stock) {
            $stock->units_manual = $units[$stock->id] ?? collect();
        }

        return $stocks;
    }
    public function getUnits()
    {
        return $this->units;
    }
}
