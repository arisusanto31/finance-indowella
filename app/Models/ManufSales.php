<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManufSales extends Model
{
    //

    protected $connection="manufSql";
    protected $table = "transactions";
    public $timestamps = true;
}
