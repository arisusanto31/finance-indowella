<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Stock extends Model
{
    //
    protected $table = 'stocks';
    public $timestamps = true;
    protected $fillable = [
        'name',
        'category_id',
        'parent_category_id',
        'unit_backend'
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
                $q->whereNull("{$alias}.is_deleted")->orWhere("{$alias}.is_deleted", false);
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
}
