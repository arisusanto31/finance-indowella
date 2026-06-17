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
    protected $signature = 'app:repair-invoice {bookid} {month}';

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
        $month = $this->argument('month');
        $bookid = $this->argument('bookid');
        Session::put('book_journal_id', $bookid);
        $month.= '-01';
        $dateAwal = createCarbon($month)->startOfMonth();
        $dateAkhir = createCarbon($month)->endOfMonth();
        $sales = SalesOrder::whereBetween('created_at', [$dateAwal, $dateAkhir])->get();
        $this->info('Found ' . $sales->count() . ' sales orders for month: ' . $month);
        foreach ($sales as $salesOrder) {
            if (!$salesOrder) {
                $this->error('Sales order not found');
                return;
            }
            $st = $salesOrder->repairInvoice();
            if (!$st) {
                $this->error('Failed to repair invoice for sales order ' . $salesOrder->sales_order_number);
                return;
            }
            $this->info('Invoice repaired successfully for sales order ' . $salesOrder->sales_order_number);
        }
    }
}
