<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PrepaidExpense extends Model
{
    //

    protected $table = "prepaid_expenses";
    public $timestamps  = true;


    protected static function booted()
    {

        static::addGlobalScope('book_journal', function ($query) {
            $from = $query->getQuery()->from ?? 'prepaid_expenses'; // untuk dukung alias `j` kalau pakai from('journals as j')
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


    public $fillable = [
        'book_journal_id',
        'name',
        'type_bdd',
        'date',
        'nilai_perolehan',
        'periode'
    ];
}
