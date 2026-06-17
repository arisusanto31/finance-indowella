<?php

namespace App\Console\Commands;

use App\Http\Controllers\JournalController;
use App\Models\InvoicePack;
use App\Models\Journal;
use App\Models\SalesOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;

class CancelPembayaranInvoice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cancel-pembayaran-invoice {bookid} {id}';

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
        Session::put('book_journal_id', $bookid);
        $saleOrder = SalesOrder::find($this->argument('id'));
        $saleOrder->repairPembayaran();
    }
}
