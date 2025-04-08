<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Customer extends Model
{
   
    protected $fillable = [
        'name',
        'address',
        'phone',
        'ktp',
        'npwp',
        'purchase_info',
        'is_deleted',     
        'deleted_at',     
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
