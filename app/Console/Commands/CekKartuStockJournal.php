<?php

namespace App\Console\Commands;

use App\Models\ChartAccountAlias;
use App\Models\Journal;
use App\Models\KartuStock;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CekKartuStockJournal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cek:kartu-stock-journal {bookid} {date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cek kartu stock journal';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $date = $this->argument('date');
        $bookid = $this->argument('bookid');
        Session::put('book_journal_id', $bookid);
        //dari kartu stock
        $indexStart =  createCarbon($date)->startOfDay()->format('ymdHis000');
        $indexEnd =  createCarbon($date)->endOfDay()->format('ymdHis999');

        $kartuStocks = KartuStock::whereBetween('index_date', [$indexStart, $indexEnd])
            ->select('id', 'mutasi_rupiah_total', 'book_journal_id');

        $kstocks = DB::table('kartu_stocks')->fromSub($kartuStocks, 'ks')
            ->leftJoin('detail_kartu_invoices as dk', function ($join) {
                $join->on('dk.kartu_id', '=', 'ks.id')
                    ->where('dk.kartu_type', KartuStock::class);
            })->leftJoin('journals as j', function ($join) {
                $join->on('j.id', '=', 'dk.journal_id');
            })
            ->select(
                'ks.id as kartu_id',
                DB::raw('sum(ks.mutasi_rupiah_total) as mutasi_rupiah_total'),
                DB::raw('j.amount_debet - j.amount_kredit as journal_amount'),
                'j.journal_number',
                'j.id as journal_id',
                DB::raw('sum(ks.mutasi_rupiah_total) - (j.amount_debet - j.amount_kredit) as selisih')
            )
            ->groupBy('j.id')
            ->havingRaw('mutasi_rupiah_total <>journal_amount')
            ->get();;

        tampilkanTableTerminal($kstocks, [
            'kartu_id' => 'center',
            'mutasi_rupiah_total' => 'right',
            'journal_amount' => 'right',
            'journal_number' => 'center',
            'journal_id' => 'center',
            'selisih' => 'right'
        ], $this);
        $this->info('jumlah kartu stock yang bermasalah ' . count($kstocks) . ', dan total selisihnya ' . $kstocks->sum('selisih'));

        $indJStart = createCarbon($date)->startOfDay()->format('ymdHis00');
        $indJEnd = createCarbon($date)->endOfDay()->format('ymdHis99');
        $allcodeks = ChartAccountAlias::where('reference_model', KartuStock::class)->pluck('code_group')->all();
        $journals = Journal::whereIn('code_group', $allcodeks)
            ->whereBetween('index_date', [$indJStart, $indJEnd])
            ->select(
                'id',
                'journal_number',
                'code_group',
                DB::raw('amount_debet - amount_kredit as journal_amount')
            );
        $journals = DB::table('journals')->fromSub($journals, 'journals')
            ->leftJoin('detail_kartu_invoices as dk', function ($join) {
                $join->on('dk.journal_id', '=', 'journals.id')
                    ->where('dk.kartu_type', KartuStock::class);
            })->leftJoin('kartu_stocks as ks', function ($join) {
                $join->on('ks.id', '=', 'dk.kartu_id');
            })->whereRaw('(journals.journal_amount) <> ks.mutasi_rupiah_total')
            ->select(
                'journals.id as journal_id',
                'journals.journal_number',
                'journals.code_group',
                DB::raw('journals.journal_amount'),
                'ks.id as kartu_id',
                DB::raw('sum(ks.mutasi_rupiah_total) as mutasi_rupiah_total'),
                DB::raw('(journals.journal_amount - sum(ks.mutasi_rupiah_total)) as selisih')
            )
            ->groupBy('journals.id')
            ->havingRaw('journals.journal_amount <> mutasi_rupiah_total')
            ->get();
        tampilkanTableTerminal($journals, [
            'journal_id' => 'center',
            'journal_number' => 'center',
            'code_group' => 'center',
            'journal_amount' => 'right',
            'kartu_id' => 'center',
            'mutasi_rupiah_total' => 'right',
            'selisih' => 'right'
        ], $this);
        $this->info('jumlah journal yang bermasalah ' . count($journals) . ', dan total selisihnya ' . $journals->sum('selisih'));
    }
}
