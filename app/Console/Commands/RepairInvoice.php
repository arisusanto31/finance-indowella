<?php

namespace App\Console\Commands;

use App\Models\SalesOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;

class RepairInvoice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:repair-invoice {bookid} {id}';

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
        $id = $this->argument('id');
        $salesOrder = SalesOrder::find($id);
        if(!$salesOrder){
            $this->error('Sales order not found');
            return;
        }
        $st = $salesOrder->repairInvoice();
        if(!$st){
            $this->error('Failed to repair invoice for sales order ' . $salesOrder->sales_order_number);
            return;
        }
        $this->info('Invoice repaired successfully for sales order ' . $salesOrder->sales_order_number);
    }
}
