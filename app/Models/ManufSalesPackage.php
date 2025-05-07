<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManufSalesPackage extends Model
{
    //

    protected $connection = "manufSql";
    protected $table = "packages";

    public function detailSales()
    {
        return $this->hasMany(ManufSales::class, 'package_id', 'id');
    }
}
