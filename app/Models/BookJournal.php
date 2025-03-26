<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookJournal extends Model
{
    protected $table = 'book_journals';
    protected $fillable = ['name', 'description', 'type', 'theme'];
    public $timestamps = true;   
    
}
