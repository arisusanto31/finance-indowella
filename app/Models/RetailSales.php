<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RetailSales extends Model
{
    //
    protected $connection = "tokoSql";
    protected $table = 'transactions';
    public $timestamps = true;

    public function toko()
    {
        return $this->belongsTo(RetailToko::class, 'toko_id', 'id');
    }
}
