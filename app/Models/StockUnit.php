<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockUnit extends Model
{
    //
    protected $table = 'stock_units';
    public $timestamps = true;

    protected $fillable = [
        'name',
        'stock_id',
        'unit',
        'konversi'
    ];
}
