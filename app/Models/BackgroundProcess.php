<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BackgroundProcess extends Model
{
    //
    protected $table = 'background_process';
    protected $fillable = [
        'monitoring_url',
        'progress',
        'description_process',
        'total_task',
        'success_task',
        'failed_task',
        'status',
        'book_journal_id'
    ];

    protected static function booted()
    {
        static::addGlobalScope('journal', function ($query) {
            $from = $query->getQuery()->from ?? 'background_process'; // untuk dukung alias `j` kalau pakai from('journals as j')
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

    public static function make($bookid,$monitoring_url, $description_process, $total_task)
    {
        $backgroundProcess = BackgroundProcess::where('monitoring_url',$monitoring_url)
            ->where('book_journal_id', $bookid)
            ->where('description_process', $description_process)
            ->first();
        if (!$backgroundProcess) {
            $backgroundProcess = BackgroundProcess::create([
                'monitoring_url' => $monitoring_url,
                'total_task' => $total_task,
                'description_process' => $description_process,
                'status' => 'processing',
                'book_journal_id' => $bookid,
            ]);
        } else {
            $backgroundProcess->success_task = 0;
            $backgroundProcess->failed_task = 0;
            $backgroundProcess->progress = 0;
            $backgroundProcess->total_task = $total_task;
            $backgroundProcess->status = 'processing';
            $backgroundProcess->save();
        }
        $theBG = BackgroundProcess::find($backgroundProcess->id);
        return $theBG;
    }
}
