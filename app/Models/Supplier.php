<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Supplier extends Model
{
    //
    protected $table='suppliers';
    public $timestamps= true;


    protected static function booted()
    {
     
        static::addGlobalScope('supplier', function ($query) {
            $from = $query->getQuery()->from ?? 'suppliers'; // untuk dukung alias `j` kalau pakai from('journals as j')
            if (Str::contains($from, ' as ')) {
                [$table, $alias] = explode(' as ', $from);
                $alias = trim($alias);
            } else {
                $alias = $from;
            }
        
            $query->whereNull("{$alias}.is_deleted");
        });
    }

}
