<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManufSales extends Model
{
    //

    protected $connection = "manufSql";
    protected $table = "transactions";
    public $timestamps = true;

    public function detailInvoices()
    {
        return $this->hasMany(ManufSalesInvoiceDetail::class, 'transaction_id', 'id');
    }
}
