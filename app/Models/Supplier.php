<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Supplier extends Model
{
    //
    protected $table = 'suppliers';
    public $timestamps = true;


    protected static function booted()
    {

        static::addGlobalScope('aktif', function ($query) {
            $from = $query->getQuery()->from ?? 'suppliers'; // untuk dukung alias `j` kalau pakai from('journals as j')
            if (Str::contains($from, ' as ')) {
                [$table, $alias] = explode(' as ', $from);
                $alias = trim($alias);
            } else {
                $alias = $from;
            }

            $query->where(function ($q) use ($alias) {
                $q->whereNull("{$alias}.is_deleted")->orWhere("{$alias}.is_deleted", 0);
            });
        });

        static::addGlobalScope('journal', function ($query) {
            $from = $query->getQuery()->from ?? 'suppliers'; // untuk dukung alias `j` kalau pakai from('journals as j')
            if (Str::contains($from, ' as ')) {
                [$table, $alias] = explode(' as ', $from);
                $alias = trim($alias);
            } else {
                $alias = $from;
            }
            $query->where(function ($q) use ($alias) {
                $q->whereNull("{$alias}.book_journal_id")
                    ->orWhere("{$alias}.book_journal_id", bookID());
            });
        });
    }



    protected $fillable = [
        'name',
        'npwp',
        'ktp',
        'cp_name',
        'phone',
        'address',
        'is_deleted',
        'book_journal_id',
    ];
}
