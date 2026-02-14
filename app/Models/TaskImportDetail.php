<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskImportDetail extends Model
{
    //
    protected $table = 'task_import_details';
    protected $fillable = [
        'book_journal_id',
        'task_import_id',
        'type',
        'payload',
        'status',
        'request_date',
        'processed_at',
        'finished_at',
    ];
    public function taskImport()
    {
        return $this->belongsTo(TaskImport::class, 'task_import_id');
    }
}
