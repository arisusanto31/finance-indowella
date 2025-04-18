<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Customer extends Model
{
   
    protected $table = 'customers';

    protected $fillable = [
        'name', 'address', 'phone', 'ktp', 'npwp', 'is_deleted', 'deleted_at'
    ];
}