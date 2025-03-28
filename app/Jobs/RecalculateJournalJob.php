<?php

namespace App\Jobs;

use App\Models\Journal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class RecalculateJournalJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 3;
    public $backoff = [120, 300, 500];
    protected $id;
    public function __construct($id)
    {
        //

        $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //

        try {
            $journal = Journal::find($this->id);

            if (!$journal) {
                throw new \RuntimeException('journal tidak ditemukan, coba lagi nanti . try recalculate journal on ' . $this->id);
            }
            $st = $journal->recalculateJournal();

            if ($st['status'] == 0) {
                throw new \RuntimeException(json_encode($st));
            }
        } catch (Throwable $th) {
            throw $th;
        }
    }
}
