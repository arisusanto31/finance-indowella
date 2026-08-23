<?php

namespace App\Console\Commands;

use App\Models\Journal;
use App\Models\KartuPiutang;
use App\Models\SalesOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ScanProblemKartuPiutang extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scan:problem-kartu-piutang {monthyear}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan for problems in Kartu Piutang';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $monthyear = $this->argument('monthyear');
        Session::put('book_journal_id', 2);
        $indexStart = createCarbon($monthyear . '-01')->startOfMonth()->format('ymdHis000');
        $indexEnd = createCarbon($monthyear . '-01')->endOfMonth()->format('ymdHis999');
        $kps = KartuPiutang::whereBetween('index_date', [$indexStart, $indexEnd])
            ->select(DB::raw('sum(amount_debet-amount_kredit) as total_amount'), 'invoice_pack_number', 'id', 'journal_number', 'sales_order_id')
            ->groupBy('invoice_pack_number')
            ->havingRaw('total_amount <> 0')
            ->get();
        tampilkanTableTerminal($kps, [
            'invoice_pack_number' => 'center',
            'total_amount' => 'right'
        ], $this);


        foreach ($kps as $kp) {
            //cek all kp di invoice itu yang ga jelas.
            $allkps = KartuPiutang::where('invoice_pack_number', $kp->invoice_pack_number)->get();
            $theso = SalesOrder::find($kp->sales_order_id);
            foreach ($allkps as $k) {
                $this->info('Checking kpid:' . $k->id . ' invoice pack number: ' . $k->invoice_pack_number . ' with ' . $k->total_amount);
                $so = SalesOrder::find($k->sales_order_id);
                if (!$so) {
                    $journal = Journal::where('journal_number', $k->journal_number)->first();
                    if (!$journal) {
                        $this->error('kartu usang kpid:' . $k->id . ' invoice pack number: ' . $k->invoice_pack_number . ' and journal number: ' . $k->journal_number);
                        $k->delete();
                        continue;
                    } else {
                        $this->error('jurnal ada tapi sales order tidak ditemukan kpid:' . $k->id . ' invoice pack number: ' . $k->invoice_pack_number . ' and journal number: ' . $k->journal_number);
                        $k->delete();
                        continue;
                    }
                }
            }
            $problem = KartuPiutang::where('invoice_pack_number', $kp->invoice_pack_number)
                ->where('amount_kredit', '<>', ($theso->total_price + $theso->total_ppn_k))
                ->where('type', 'pelunasan')
                ->get();
            tampilkanTableTerminal($problem, [
                'id' => 'center',
                'type' => 'center',
                'description' => 'left',
                'amount_kredit' => 'right'
            ], $this);
            foreach ($problem as $p) {
                $p->delete();
                $this->info('Deleted Kartu Piutang ID: ' . $p->id . ' with amount_kredit: ' . $p->amount_kredit);
            }
        }
    }
}
