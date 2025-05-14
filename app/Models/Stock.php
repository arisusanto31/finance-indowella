<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class Stock extends Model
{
    //
    protected $table = 'stocks';
    public $timestamps = true;
    protected $fillable = [
        'name',
        'category_id',
        'parent_category_id',
        'unit_backend',
        'unit_default',
        'book_journal_id',
        'reference_stock_id',
        'reference_stock_type',
        'type'
    ];


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
                $q->whereNull("{$alias}.is_deleted")->orWhere("{$alias}.is_deleted", 0);
            });
        });

        static::addGlobalScope('journal', function ($query) {
            $from = $query->getQuery()->from ?? 'stocks'; // untuk dukung alias `j` kalau pakai from('journals as j')
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

    public function units()
    {
        return $this->hasMany('App\Models\StockUnit', 'stock_id')->whereNull('is_deleted');
    }


    public function category()
    {
        return $this->belongsTo('App\Models\StockCategory', 'category_id');
    }
    public function parentCategory()
    {
        return $this->belongsTo('App\Models\StockCategory', 'parent_category_id');
    }
    public function trashed($q)
    {
        $q->where('stocks.is_deleted', true);
    }



    public function getItem(Request $request)
    {
        $search = $request->get('search');

        $stocks = Stock::with('category')
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->select('id', 'name', 'category_id')
            ->limit(20)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'text' => $item->name . ' - ' . optional($item->category)->name,
                ];
            });

        return ['results' => $stocks];
    }

    public function getLastHPP($unit)
    {
        $lastCard = KartuStock::where('stock_id', $this->id)->orderBy('id', 'desc')->first();
        if (!$lastCard) {
            return 0;
        }
        $stockUnit = $this->units()->where('unit', $unit)->first();
        return $lastCard->saldo_rupiah_total / $lastCard->saldo_qty_backend * $stockUnit->konversi;
    }
}
