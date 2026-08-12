<?php

namespace App\Console\Commands;

use App\Models\InvoicePurchaseDetail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class RepairINdexInvoicePurchase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repair:index-invoice-purchase {monthyear}';

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
        Session::put('book_journal_id',2);
        $monthyear = $this->argument('monthyear');
        $startDate = createCarbon($monthyear . '-01')->startOfMonth();
        $endDate = createCarbon($monthyear . '-01')->endOfMonth();
        //kita restart aja dulu semua
        DB::statement('UPDATE invoice_purchase_details SET index_date = null WHERE book_journal_id=2 AND created_at BETWEEN "'.$startDate.'" AND "'.$endDate.'"');
        //okee disini kita coba isi dddata dengan yang baru.
        $invoicePurchase = InvoicePurchaseDetail::whereBetween('created_at', [$startDate, $endDate])->get();
        foreach ($invoicePurchase as $ip) {
            $ip->fillKartuStockID();
            $this->info('Repair Kartu Stock ID for Invoice Purchase Detail ID: ' . $ip->id . ' on index' . $ip->index_date);
        }
    }
}


