<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockCategory extends Model
{
    //
    protected $table='stock_categories';
    public $timestamps= true;
    protected $fillable = [
        'name',
        'parent_id',
     
    ];


}
