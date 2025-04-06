<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class StockUnit extends Model
{
    //
    protected $table = 'stock_units';
    public $timestamps = true;

    protected $fillable = [
        'stock_id',
        'unit',
        'konversi'
    ];

    protected function unit(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => ucwords(strtolower($value)),
        );
    }
}
