<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;
use App\Models\Journal;
use Illuminate\Support\Facades\DB;

class SearchManualJournalProblem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search:manual-journal-problem {bookid}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Search for manual journal problems';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        Session::put('book_journal_id', $this->argument('bookid'));
        $lastSaldo = [];
        $problemsLag = [];
        $problemsManual = [];
        $fixJournals = Journal::where('book_journal_id', $this->argument('bookid'))->select(
            'code_group',
            'book_journal_id',
            'index_date',
            'amount_saldo',
            DB::raw('CASE WHEN code_group < 200000 THEN amount_debet - amount_kredit ELSE
            amount_kredit - amount_debet END as amount_journal'),
            'journal_identifier',
            DB::raw('LAG(amount_saldo) OVER (PARTITION BY book_journal_id,code_group ORDER BY index_date) as last_saldo')
        );
        
        
        $fixJournals= Journal::fromSub($fixJournals, 'journals')->orderBy('journal_identifier')->chunkById(100000, function ($journals) use (&$lastSaldo, &$problemsLag, &$problemsManual) {
            foreach ($journals as $journal) {
                if (!isset($lastSaldo[$journal->code_group])) {
                    $lastSaldo[$journal->code_group] = $journal->amount_saldo;
                    $problemsLag[$journal->code_group] = null;
                    $problemsManual[$journal->code_group] = null;
                } else {

                    $amanLag = true;
                    $amanManual = true;
                    if (abs(($journal->amount_journal + $journal->last_saldo) - $journal->amount_saldo) > 1) {
                        $amanLag = false;
                    }
                    if (abs(($journal->amount_journal + $lastSaldo[$journal->code_group]) - $journal->amount_saldo) > 1) {
                        $amanManual = false;
                    }
                    $lastSaldo[$journal->code_group] += $journal->amount_journal;
                    if ($problemsManual[$journal->code_group] === null && $amanManual === false) {
                        $journal->manual_seharusnya = $journal->amount_journal + $lastSaldo[$journal->code_group];
                        $problemsManual[$journal->code_group] = $journal;
                    }
                    if ($problemsLag[$journal->code_group] === null && $amanLag === false) {
                        $journal->lag_seharusnya = $journal->amount_journal + $journal->last_saldo;
                        $problemsLag[$journal->code_group] = $journal;
                    }
                    // $strProblemLag = $amanLag ? '✅' : '❌';
                    // $strProblemManual = $amanManual ? '✅' : '❌';
                    // $this->info("Journal: {$journal->journal_identifier} | Problem Lag: {$strProblemLag} | Problem Manual: {$strProblemManual}");
                }
            }
        }, 'journal_identifier');

        $this->info('Summary Problem');
        $this->info('Problem Lag:');
        $plags = collect($problemsLag)->filter(function ($problem) {
            return $problem !== null;
        })->values();
        tampilkanTableTerminal(
            $plags,
            [
                'code_group' => 'center',
                'index_date' => 'center',
                'amount_journal' => 'right',
                'lag_seharusnya' => 'right',
                'amount_saldo' => 'right',
                'last_saldo' => 'right'
            ],
            $this
        );
       
        $this->info("");
        $this->info('Problem Manual:');
        $pManuals= collect($problemsManual)->filter(function ($problem) {
            return $problem !== null;
        })->values();
        tampilkanTableTerminal(
            $pManuals,
            [
                'code_group' => 'center',
                'index_date' => 'center',
                'amount_journal' => 'right',
                'manual_seharusnya' => 'right',
                'amount_saldo' => 'right'
            ],
            $this
        );
       
    }
}
