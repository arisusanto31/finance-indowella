<?php

namespace App\Console\Commands;

use App\Models\InvoicePurchaseDetail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;

class FillTheInvoicePurchase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fill:the-invoice-purchase {id}';

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
        Session::put('book_journal_id', 2);
        $id = $this->argument('id');
        $invoicePurchase = InvoicePurchaseDetail::find($id);
        if ($invoicePurchase) {
            $invoicePurchase->fillKartuStockID();
            $this->info('Updated Invoice Purchase Detail ID: ' . $invoicePurchase->id . ' with Kartu Stock ID: ' . $invoicePurchase->kartu_stock_id);
        } else {
            $this->error('Invoice Purchase Detail not found for ID: ' . $id);
        }
    }
}
