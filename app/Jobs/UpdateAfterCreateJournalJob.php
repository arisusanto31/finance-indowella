<?php

namespace App\Jobs;

use App\Models\Journal;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Session;

class UpdateAfterCreateJournalJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    protected $journalID, $bookID;
    public function __construct($journalID, $bookID)
    {
        //
        $this->journalID = $journalID;
        $this->bookID = $bookID;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        try {
            $bookID = $this->bookID ?? 2;
            Session::put('book_journal_id', $bookID);
            $journal = Journal::withoutGlobalScopes()->where('id', intval($this->journalID))->first();
            $journal->updateAfterCreate();
            info('journal-job: success');
        } catch (\Exception $e) {
            info('journal-job: error on update after create journal job ' . $e->getMessage());
        }
    }
}
