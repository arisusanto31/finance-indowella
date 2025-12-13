<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LinkTokoParent extends Model
{
    //

    protected $table = "link_toko_parents";
    protected $fillable = [
        'book_journal_id',
        'parent_id',
        'parent_type',
        'toko_id',
    ];

    public function parent()
    {
        return $this->morphTo('parent', 'parent_type', 'parent_id');
    }   
    public function toko()
    {
        return $this->belongsTo(Toko::class, 'toko_id');
    }

    protected static function booted()
    {
        static::addGlobalScope('journal', function ($query) {
            $from = $query->getQuery()->from ?? 'link_toko_parents'; // untuk dukung alias `j` kalau pakai from('journals as j')
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
}
