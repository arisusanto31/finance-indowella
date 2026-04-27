<?php

namespace App\Console\Commands;

use App\Models\ChartAccountAlias;
use App\Models\Journal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class EvaluasiHPP extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'evaluasi:hpp {bookid} {month} {year}';

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
        $date= createCarbon($this->argument('year') . '-' . $this->argument('month') . '-01');
        $indexStart = $date->copy()->startOfMonth()->format('ymdHis00');
        $indexEnd= $date->copy()->endOfMonth()->format('ymdHis99');
        $bookid = $this->argument('bookid');
        Session::put('book_journal_id', $bookid);
        $codeKartuStock= ChartAccountAlias::where('book_journal_id', $bookid)->where('code_group','like','14%')->pluck('code_group')->all();
        $codeHPP= ChartAccountAlias::where('book_journal_id', $bookid)->where('code_group','like','6%')->pluck('code_group')->all();
        $journalPersediaan=Journal::where('index_date', '>', $indexStart)->where('index_date', '<', $indexEnd)
            ->whereIn('code_group', $codeKartuStock)
            ->select('lawan_code_group',DB::raw('SUM(amount_kredit) as total_kredit'))
            ->groupBy('lawan_code_group')->get();
        $journalHPP=Journal::where('index_date', '>', $indexStart)->where('index_date', '<', $indexEnd)
            ->whereIn('code_group', $codeHPP)
            ->select('lawan_code_group',DB::raw('SUM(amount_debet) as total_debet'))
            ->groupBy('lawan_code_group')->get();
        $this->info('Persediaan');
        tampilkanTableTerminal($journalPersediaan->toArray(),[
            'lawan_code_group' => 'center',
            'total_kredit' => 'right'
        ], $this);
        $this->info('HPP');
        tampilkanTableTerminal($journalHPP->toArray(),[
            'lawan_code_group' => 'center',
            'total_debet' => 'right'
        ], $this);
        $this->info('Evaluasi');
    }
}
