<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Karyawan extends Model
{
    protected $fillable = [
        'nama',
        'book_journal_id',
        'npwp',  
        'nik',     
        'jabatan', 
        'date_masuk', 
        'date_keluar', 
         
    ];

    protected static function booted()
    {
        static::addGlobalScope('journal', function ($query) {
            $from = $query->getQuery()->from ?? 'karyawans';
            if (Str::contains($from, ' as ')) {
                [$table, $alias] = explode(' as ', $from);
                $alias = trim($alias);
            } else {
                $alias = $from;
            }

            $query->where(function ($q) use ($alias) {
                $q->whereNull("{$alias}.book_journal_id")
                    ->orWhere("{$alias}.book_journal_id", session('book_journal_id'));
            });
        });
    }
}
