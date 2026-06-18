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
        $month .= '-01';
        $dateAwal = createCarbon($month)->startOfMonth();
        $dateAkhir = createCarbon($month)->endOfMonth();
        $sales = SalesOrder::whereBetween('created_at', [$dateAwal, $dateAkhir])->where('status_delivery', '<>', 'TERKIRIM 100%')->get();
        $this->info('Found ' . $sales->count() . ' sales orders for month: ' . $month);
        $totalcount = $sales->count();
        $i = 0;
        $countsuccess = 0;
        $countfailed = 0;
        foreach ($sales as $salesOrder) {
            $i++;
            if (!$salesOrder) {
                $this->error('Sales order not found');
                $countfailed++;

                // return;
            } else {
                $st = $salesOrder->repairInvoice();
                if (!$st) {
                    $this->error('Failed to repair invoice for sales order ' . $salesOrder->sales_order_number);
                    $countfailed++;
                    // return;
                } else {
                    $countsuccess++;
                }
                $this->info('Invoice repaired successfully for sales order ' . $salesOrder->sales_order_number);
            }
            $progress = round(($i / $totalcount) * 100, 2);
            $this->info('Progress: ' . $progress . '%' . ' success : ' . $countsuccess . ' failed : ' . $countfailed);
        }
    }
}
