<?php

namespace App\Console\Commands;

use App\Http\Controllers\JournalController;
use App\Models\Journal;
use App\Models\JournalKey;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class FixProblemSaldo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-problem-saldo {bookid} {date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix problem saldo command';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $bookid = $this->argument('bookid');
        Session::put('book_journal_id', $bookid);
        $date = $this->argument('date') ?? carbonDate()->format('Y-m-d');
        $kunciTerakhir = JournalKey::orderBy('key_at', 'desc')->first();
        $keyAt = $kunciTerakhir ? $kunciTerakhir->key_at : createCarbon($date)->startOfYear();

        $indexDateJournal = createCarbon($keyAt)->format('ymdHis00');
        $indexDateKartu = createCarbon($keyAt)->format('ymdHis000');
        $indexDateJournalEnd = createCarbon($date)->addMonths(2)->format('ymdHis99');
        $indexDateKartuEnd = createCarbon($date)->addMonths(2)->format('ymdHis999');
        // $count= Journal::whereBetween('index_date', [$indexDateJournal, $indexDateJournalEnd])->count();
        // $this->info('jumlah journal yang dicek ' . $count);
        $this->info('mencari index date journal antara ' . $indexDateJournal . ' dan ' . $indexDateJournalEnd);
        $problem = JournalController::cariProblemJournal2($indexDateJournal, $indexDateJournalEnd);
        if ($problem['status'] == 1) {
            $journals = $problem['msg'];
            $this->info('jumlah journal yang bermasalah ' . count($journals));
            foreach ($journals as $journal) {
                $st = $journal->recalculateJournal();
                if ($st['status'] == 1) {
                    info('journal id ' . $journal->id . ' recalculated');
                } else {
                    info('journal id ' . $journal->id . ' failed to recalculate');
                }
            }
        }

        $kartus = [
            'KartuBDP',
            'KartuStock',
            'KartuBahanJadi',
            'KartuHutang',
            'KartuPiutang',
            'KartuDPSales',
            'KartuInventory',
            'KartuPerpaidExpense'
        ];
        foreach ($kartus as $kartu) {
            $kps = JournalController::cariProblemKartu($kartu, $indexDateKartu);
            if ($kps['status'] == 1) {
                $problem = $kps['msg'];
                $this->info('jumlah ' . $kartu . ' yang bermasalah ' . count($problem));
                foreach ($problem as $p) {

                    $res = JournalController::fixProblemKartu(new Request([
                        'model' => $kartu,
                        'id' => $p->id
                    ]));
                    if ($res['status'] == 1) {
                        info($kartu . ' id ' . $p->id . ' fixed');
                    } else {
                        info($kartu . ' id ' . $p->id . ' failed to fix');
                    }
                }
            }
        }
        $this->info('done');
    }
}
