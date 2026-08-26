<?php

namespace App\Console\Commands;

use App\Models\Journal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;

class FillLawanJournalID extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fill-lawan-journal-id';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        for ($i = 1; $i <= 2; $i++) {
            Session::put('book_journal_id', $i);
            $journals = Journal::whereNull('journal_lawan_id')->get();
            $this->info('ditemukan ' . count($journals) . ' jurnal yang belum memiliki lawan journal id untuk book_journal_id: ' . $i);
            foreach ($journals as $j) {
                $j->updateLawanID();
                $this->info("book " . $i . " - Updated journal id: " . $j->id . " with lawan journal id: " . $j->journal_lawan_id);
            }
        }
    }
}
