<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CekNilaiTutupJurnal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cek:nilai-tutup-jurnal {bookid} {monthyear}';

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
        $monthyear = $this->argument('monthyear');
        Session::put('book_journal_id', $bookid);
        $theLastDate = createCarbon($monthyear)->addMonth()->format('ymdHis') . '00';
        $subquery = DB::table('journals')
            ->where('book_journal_id', book()->id)
            ->where('index_date', '<', (float)$theLastDate)->whereRaw('CONVERT(code_group, UNSIGNED) > ?', [400000])
            ->select(DB::raw('MAX(journal_identifier)'))->groupBy('code_group');


        $chartAccounts = DB::table('journals as j')
            ->whereIn('j.journal_identifier', $subquery)
            ->leftJoin('chart_account_aliases as ca', function ($join) {
                $join->on('j.code_group', '=', 'ca.code_group')
                    ->where('ca.book_journal_id', book()->id);
            })
            ->where('ca.code_group', '>=', 400000)->where('ca.is_child', 1)
            ->whereNull('j.tag')
            ->select(
                'ca.name',
                'ca.id',
                'ca.code_group',
                DB::raw('CAST(ROUND(COALESCE(j.amount_saldo,0),2) AS DECIMAL(15,2)) AS saldo'),
                'ca.is_child'
            )
            ->orderBy('ca.code_group')->get();

        tampilkanTableTerminal($chartAccounts, [
            'name' => 'left',
            'id' => 'center',
            'code_group' => 'center',
            'saldo' => 'right',
            'is_child' => 'center'
        ], $this);
    }
}
