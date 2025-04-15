<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    protected $fillable = [
        'nama', 'npwp', 'nik', 'jabatan', 'date_masuk', 'date_keluar'
    ];
}


