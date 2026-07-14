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
        $journals = Journal::select(
            'code_group',
            'index_date',
            'amount_saldo',
            DB::raw('CASE WHEN code_group < 200000 THEN amount_debet - amount_kredit ELSE
            amount_kredit - amount_debet END as amount_journal'),
            'journal_identifier',
            DB::raw('coalesce(LAG(amount_saldo) OVER (PARTITION BY code_group ORDER BY index_date), 0) as last_saldo')
        )->chunkById(2000, function ($journals) use (&$lastSaldo,&$problemsLag,&$problemsManual) {
            foreach ($journals as $journal) {
                if (!isset($lastSaldo[$journal->code_group])) {
                    $lastSaldo[$journal->code_group] = $journal->amount_saldo;
                    $problemsLag[$journal->code_group] = null;
                    $problemsManual[$journal->code_group] = null;
                } else {

                    $amanLag = true;
                    $amanManual = true;
                    if ($journal->amount_journal + $journal->last_saldo != $journal->amount_saldo) {
                        $amanLag = false;
                    }
                    if ($journal->amount_journal + $lastSaldo[$journal->code_group] != $journal->amount_saldo) {
                        $amanManual = false;
                    }
                    $lastSaldo[$journal->code_group] += $journal->amount_journal;
                    if ($problemsManual[$journal->code_group] === null && $amanManual === false) {
                        $problemsManual[$journal->code_group] = $journal;
                    }
                    if ($problemsLag[$journal->code_group] === null && $amanLag === false) {
                        $problemsLag[$journal->code_group] = $journal;
                    }
                    $strProblemLag = $amanLag ? '✅' : '❌';
                    $strProblemManual = $amanManual ? '✅' : '❌';
                    $this->info("Journal: {$journal->journal_identifier} | Problem Lag: {$strProblemLag} | Problem Manual: {$strProblemManual}");
                }
            }
        }, 'journal_identifier');

        $this->info('Summary Problem');
        $this->info('Problem Lag:');
        foreach ($problemsLag as $codeGroup => $problem) {
            if ($problem !== null) {
                $this->info("Code Group: {$codeGroup} | Journal: {$problem->journal_identifier} | Index Date: {$problem->index_date} | Amount Journal: {$problem->amount_journal} | Last Saldo: {$problem->last_saldo} | Amount Saldo: {$problem->amount_saldo}");
            }
        }

        $this->info('\nProblem Manual:');
        foreach ($problemsManual as $codeGroup => $problem) {
            if ($problem !== null) {
                $this->info("Code Group: {$codeGroup} | Journal: {$problem->journal_identifier} | Index Date: {$problem->index_date} | Amount Journal: {$problem->amount_journal} | Last Saldo: {$lastSaldo[$codeGroup]} | Amount Saldo: {$problem->amount_saldo}");
            }
        }
    }
}
