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
    protected $signature = 'fix:miss-match-pembelian {bookid} {monthyear}';
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
        $monthyear = $this->argument('monthyear');
        $date = createCarbon($monthyear);
        $dateStart = $date->copy()->startOfMonth();
        $dateEnd = $date->copy()->endOfMonth();
        $bookID = $this->argument('bookid');
        Session::put('book_journal_id', $bookID);
        $query = DB::select('select sum(inv.mutasi_rupiah_total) as amount_inv,inv.id,(j.amount_debet-j.amount_kredit) as amount_journal,j.id as 
          jk_id, inv.journal_id from kartu_stocks as inv left join journals as j on j.id= inv.journal_id  WHERE inv.book_journal_id=? 
          AND inv.purchase_order_id is not null
          AND inv.created_at BETWEEN ? AND ? 
          group by inv.journal_id;', [$bookID, $dateStart, $dateEnd]);
        $totalSelisih = 0;
        $totalAmountt = 0;
        foreach ($query as $row) {
            $this->info("Invoice ID: {$row->id}, jID: {$row->jk_id}, JournalID: {$row->journal_id}, Amount Invoice: {$row->amount_inv}, Amount Journal: {$row->amount_journal}, selisih " . ($row->amount_inv - $row->amount_journal));
            $totalSelisih += ($row->amount_inv - $row->amount_journal);
            $totalAmountt += $row->amount_inv;
        }

        $this->info("Total selisih: " . format_price($totalSelisih));
        $this->info("Total amount invoice: " . format_price($totalAmountt));
        $indexStart = $dateStart->format('ymdHis00');
        $indexEnd = $dateEnd->format('ymdHis99');
        $this->info('==========================================');
        $this->info("\n\n");

        $query = DB::select('select i.id,i.total_price,i.invoice_pack_number,ks.id as ks_id from invoice_purchase_details as i 
          left join kartu_stocks as ks on ks.purchase_order_id=i.id  
          where i.index_date BETWEEN ? AND ? and i.book_journal_id=? and ks.id is null;', [$indexStart, $indexEnd, $bookID]);
        foreach ($query as $row) {
            $this->info("Invoice Purchase Detail ID: {$row->id}, Total Price: {$row->total_price}, Invoice Pack Number: {$row->invoice_pack_number}, Kartu Stock ID: {$row->ks_id}");
        }


        $query2 = DB::select('select sum(amount_debet-amount_kredit) as sum,lawan_code_group,code_group from journals where book_journal_id=? and code_group in
         (140001,140002) and index_date BETWEEN ? AND ?  and amount_debet > 0 group by lawan_code_group;', [$bookID, $indexStart, $indexEnd]);
        $lawanPersediaan = 0;
        $lawanNonPersediaan = 0;
        foreach ($query2 as $row) {
            $this->info("Lawan Code Group: {$row->lawan_code_group}, Code Group: {$row->code_group}, Amount: {$row->sum}");
            if ($row->lawan_code_group > 140000 && $row->lawan_code_group < 150000) {
                $lawanPersediaan += $row->sum;
            } else {
                $lawanNonPersediaan += $row->sum;
            }
        }
        $this->info("Lawan Persediaan: " . format_price($lawanPersediaan));
        $this->info("Lawan Non Persediaan: " . format_price($lawanNonPersediaan));
    }
}
