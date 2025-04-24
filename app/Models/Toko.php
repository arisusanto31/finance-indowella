<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Toko extends Model
{
    //
    protected $table = "tokoes";
    public $timestamps = true;
    public $fillable = [
        'book_journal_id',
        'name',
        'phone',
        'address',
    ];
    protected static function booted()
    {
        static::addGlobalScope('book_journal', function ($query) {
            $from = $query->getQuery()->from ?? 'tokoes'; // untuk dukung alias `j` kalau pakai from('journals as j')
            $query->where(function ($q) use ($from) {
                $q->whereNull("{$from}.book_journal_id")
                    ->orWhere("{$from}.book_journal_id", session('book_journal_id'));
            });
        });
        static::addGlobalScope('aktif', function ($query) {
            $from = $query->getQuery()->from ?? 'tokoes'; // untuk dukung alias `j` kalau pakai from('journals as j')
            $query->whereNull("{$from}.is_deleted");
        });
    }
}
