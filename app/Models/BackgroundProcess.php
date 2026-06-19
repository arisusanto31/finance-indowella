<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
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

    public static function make($bookid, $monitoring_url, $description_process, $total_task)
    {
        $lock = Cache::lock('bg_process_' . $bookid . '_' . $description_process, 10);
        if ($lock->get()) {
            $backgroundProcess = BackgroundProcess::where('monitoring_url', $monitoring_url)
                ->where('book_journal_id', $bookid)
                ->where('description_process', $description_process)
                ->first();
            if (!$backgroundProcess) {
                $backgroundProcess = BackgroundProcess::create([
                    'monitoring_url' => $monitoring_url,
                    'total_task' => $total_task ?? 1,
                    'description_process' => $description_process,
                    'status' => 'processing',
                    'book_journal_id' => $bookid,
                ]);
            } else {
                $backgroundProcess->success_task = 0;
                $backgroundProcess->failed_task = 0;
                $backgroundProcess->progress = 0;
                $backgroundProcess->total_task = $total_task == null ? $backgroundProcess->total_task + 1 : $total_task;
                $backgroundProcess->status = 'processing';
                $backgroundProcess->save();
            }
            $lock->release();
        }
        $theBG = BackgroundProcess::find($backgroundProcess->id);
        return $theBG;
    }

    public static function failedTask($bookid, $description_process)
    {
        $lock = Cache::lock('bg_process_' . $bookid . '_' . $description_process, 10);
        if ($lock->get()) {
            $bg = BackgroundProcess::where('description_process', $description_process)->first();
            $bg->failed_task = $bg->failed_task + 1;
            $bg->hitungProgress();
            $bg->save();
            $lock->release();
        }
    }

    public function failed()
    {
        $lock = Cache::lock('bg_process_' . $this->book_journal_id . '_' . $this->description_process, 10);
        if ($lock->get()) {
            $bg = BackgroundProcess::find($this->id);
            $bg->failed_task = $bg->failed_task + 1;
            $bg->hitungProgress();
            $bg->save();
            $lock->release();
        }
    }

    public static function successTask($bookid, $description_process)
    {
        $lock = Cache::lock('bg_process_' . $bookid . '_' . $description_process, 10);
        if ($lock->get()) {
            $bg = BackgroundProcess::where('description_process', $description_process)->first();
            $bg->success_task = $bg->success_task + 1;
            $bg->hitungProgress();
            $bg->save();
            $lock->release();
        }
    }


    public function success()
    {
        $lock = Cache::lock('bg_process_' . $this->book_journal_id . '_' . $this->description_process, 10);
        if ($lock->get()) {
            $bg = BackgroundProcess::find($this->id);
            $bg->success_task = $bg->success_task + 1;
            $bg->hitungProgress();
            $bg->save();
            $lock->release();
        }
    }


    public function hitungProgress()
    {
        $this->progress = ($this->success_task + $this->failed_task) / $this->total_task * 100;
    }
}
