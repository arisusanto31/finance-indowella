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
    protected $journalID;
    protected $bookID;
    public function __construct($journalID,$bookID)
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
        Session::put('book_journal_id', $this->bookID);
        $journal= Journal::find($this->journalID);
        if($journal){
            $journal->updateAfterCreate();
        }
        else{
            throw new \Exception("Journal with ID {$this->journalID} not found.");
        }
    }
}
