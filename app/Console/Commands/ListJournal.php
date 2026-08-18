<?php

namespace App\Console\Commands;

use App\Models\Journal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ListJournal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'list:journal {bookid} {startdate}';

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
        $bookid = $this->argument('bookid');
        $startdate = $this->argument('startdate');
        $indexAwal = createCarbon($startdate)->format('ymdHis00');
        $indexAkhir = createCarbon($startdate)->addDay()->format('ymdHis99');


        Session::put('book_journal_id', $bookid);
        $lastJournal = Journal::where('index_date', '<', $indexAwal)
            ->select(DB::raw('max(index_date) as max_index_date'), 'code_group')
            ->groupBy('code_group');
        $theLastJournal = Journal::joinSub($lastJournal, 'last', function ($join) {
            $join->on('last.code_group', '=', 'journals.code_group')
                ->on('last.max_index_date', '=', 'journals.index_date');
        })->select(
            'journals.id',
            'journals.book_journal_id',
            'journals.code_group',
            'journals.index_date',
            'journals.tag',
            'journals.journal_number',
            DB::raw('CASE WHEN journals.code_group < 200000 THEN journals.amount_debet-journals.amount_kredit ELSE journals.amount_kredit-journals.amount_debet END as amount_journal'),
            'journals.amount_saldo'
        );
        $journals = Journal::whereBetween('index_date', [$indexAwal, $indexAkhir])
            ->select(
                'id',
                'book_journal_id',
                'code_group',
                'index_date',
                'tag',
                'journal_number',
                DB::raw('CASE WHEN code_group < 200000 THEN amount_debet-amount_kredit ELSE amount_kredit-amount_debet END as amount_journal'),
                'amount_saldo',
            )->union($theLastJournal);

        $journals = Journal::fromSub($journals, 'journals')
            ->select(
                'journals.*',
                DB::raw('COALESCE(LAG(amount_saldo) OVER (PARTITION BY code_group ORDER BY index_date),0) as last_saldo')
            )->get();

        $file = public_path('files/list_journal.csv');
        $handle = fopen($file, 'w');
        
        $data=[];
        $data[]=['id','book_journal_id','code_group','index_date','tag','journal_number','amount_journal','amount_saldo','last_saldo'];        
        foreach ($journals as $journal) {
            $data[] = [
                $journal->id,
                $journal->book_journal_id,
                $journal->code_group,
                $journal->index_date,
                $journal->tag,
                $journal->journal_number,
                $journal->amount_journal,
                $journal->amount_saldo,
                $journal->last_saldo,
            ];
        }

        foreach ($data as $row) {
            fputcsv($handle, $row, ','); // pakai ; biar cocok Excel Indonesia
        }
        fclose($handle);
        $this->info("CSV dibuat: $file");
        // $datamin = Journal::fromSub($journals, 'journals')
        //     ->whereRaw('last_saldo + amount_journal != amount_saldo')
        //     ->where('tag', '<>', 'opening 01/2026')
        //     ->select('*', DB::raw('amount_journal + last_saldo - amount_saldo as selisih'))
        //     ->get();
    }
}
