<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class FixMissMatchInvoicePembelian extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:miss-match-pembelian {bookid}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix miss match invoice pembelian for a specific book ID';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //

        $bookID = $this->argument('bookid');
        Session::put('book_journal_id',$bookID);
        $query= DB::select('select sum(inv.mutasi_rupiah_total) as amount_inv,inv.id,(j.amount_debet-j.amount_kredit) as amount_journal,j.id as 
          jk_id, inv.journal_id from kartu_stocks as inv left join journals as j on j.id= inv.journal_id  WHERE inv.book_journal_id=? 
          AND inv.purchase_order_id is not null
          AND inv.created_at BETWEEN "2026-01-01 00:00:00" AND "2026-01-31 23:59:59" 
          group by inv.journal_id;', [$bookID]);
          $totalSelisih=0;
          $totalAmountt=0;
        foreach ($query as $row) {
            $this->info("Invoice ID: {$row->id}, jID: {$row->jk_id}, JournalID: {$row->journal_id}, Amount Invoice: {$row->amount_inv}, Amount Journal: {$row->amount_journal}, selisih " . ($row->amount_inv - $row->amount_journal));
            $totalSelisih += ($row->amount_inv - $row->amount_journal);
            $totalAmountt += $row->amount_inv;
        }

        $this->info("Total selisih: " . format_price($totalSelisih));
        $this->info("Total amount invoice: " . format_price($totalAmountt));
    }
}
