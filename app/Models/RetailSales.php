<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RetailSales extends Model
{
    //
    protected $connection = "retailSql";
    protected $table= 'transactions';
    public $timestamps = true;
}
