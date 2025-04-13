<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Customer extends Model
{
    // ✅ Kolom-kolom yang boleh diisi secara massal
    public $timestamps = true;
    protected $table='customers';
    protected $fillable = [
        'name',
        'address',
        'phone',
        'ktp',
        'npwp',
        'is_deleted',     // optional, kalau kamu set manual
        'deleted_at',     // optional, kalau kamu set manual
    ];

    protected static function booted()
    {
        static::addGlobalScope('customer', function ($query) {
            $from = $query->getQuery()->from ?? 'customers';
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
