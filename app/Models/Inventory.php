<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    //
    protected $table = "inventories";
    public $timestamps  = true;

    public $fillable = [
        'name',
        'type_aset',
        'keterangan_qty_unit',
        'date',
        'nilai_perolehan',
        'periode'
    ];
}
