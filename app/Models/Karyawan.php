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
        'is_deleted',
         
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
    
            $sessionId = session('book_journal_id');
    
            $query->where(function ($q) use ($alias, $sessionId) {
                if ($sessionId) {
                    $q->whereNull("{$alias}.book_journal_id")
                      ->orWhere("{$alias}.book_journal_id", $sessionId);
                } else {
                    $q->whereNull("{$alias}.book_journal_id");
                }
            })->where("{$alias}.is_deleted", 0);
        });
    }
    
}