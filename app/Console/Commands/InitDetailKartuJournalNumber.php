<?php

namespace App\Console\Commands;

use App\Models\Journal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;

class InitDetailKartuJournalNumber extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init:detail-kartu-journal-number {bookid} {number?}';

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
        $number = $this->argument('number');
        $bukuId = $this->argument('bookid');
        Session::put('book_journal_id', $bukuId);
        if ($number) {
            $journals = Journal::where('journal_number', $number)->get();
        } else
            $journals = Journal::orderBy('id')
                ->get();

        if (! $this->confirm('kamu akan proses ' . $journals->count() . ' journal, lanjutkan?')) {
            $this->info('proses dibatalkan!');
            return;
        }

        foreach ($journals as $journal) {
            $St = $journal->createKartuLink();
            // $this->info('hasil proses: '.json_encode($St));
            if ($St['status'] == 0) {
                $this->error('Gagal proses journal ID: ' . $journal->id . ' pesan: ' . $St['msg']);
                continue;
            } else
                $this->info('Processed journal ID: ' . $journal->id);
        }
    }
}
