<?php

namespace App\Console\Commands;

use App\Models\SalesOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;

class UpdateReadyStockSalesOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:ready-stock-sales-order {bookid} {monthyear}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update ready stock status for sales orders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $monthyear = $this->argument('monthyear') . '-01';
        $startDate = createCarbon($monthyear)->startOfMonth();
        $endDate = createCarbon($monthyear)->endOfMonth();
        $bookid = $this->argument('bookid');
        Session::put('book_journal_id', $bookid);
        $salesOrders =  SalesOrder::where('created_at', '>=', $startDate)
            ->where('created_at', '<', $endDate)
            ->where('status_delivery', '<>', 'TERKIRIM 100%')
            ->get();

        $this->info('kamu akan update sebanyak ' . count($salesOrders) . ' sales order');
        foreach ($salesOrders as $so) {
            $st = $so->updateReadyStock();
            $this->info(json_encode($st));
            $this->info('ready stock sales order ' . $so->is_ready_stock);
        }
    }
}
