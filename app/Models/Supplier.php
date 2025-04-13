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
            $query->where("{$alias}.is_deleted", '=', 0);

        });
    }

    

    protected $fillable = [
        'name',
        'npwp',
        'ktp',
        'cp_name',
        'phone',
        'address',
        'is_deleted',
    ];

}
