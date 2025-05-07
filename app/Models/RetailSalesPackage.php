<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RetailSalesPackage extends Model
{
    //

    protected $connection = "tokoSql";
    protected $table = 'packages';
    public $timestamps = true;


    public function detailSales()
    {
        return $this->hasMany(RetailSales::class, 'package_id', 'id')->where('is_ppn',1);
    }
}
