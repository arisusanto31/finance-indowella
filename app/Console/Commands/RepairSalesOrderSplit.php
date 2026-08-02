<?php

namespace App\Console\Commands;

use App\Models\SalesOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;

class RepairSalesOrderSplit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repair:sales-order-split';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Repair sales order split';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        Session::put('book_journal_id', 2);
        $salesOrders = SalesOrder::whereDate('updated_at', '2026-08-03')->where('created_at', '>', '2026-08-01')->get();
        foreach ($salesOrders as $so) {
            $draftNumber = $so->draft_number;
            $ambilDraft = substr($draftNumber, 0, -2);
            $thesale = SalesOrder::where('draft_number', $ambilDraft)->first();
            if ($thesale) {
                $so->created_at = $thesale->created_at;
                $so->save();
                $this->info('Sales order ' . $so->sales_order_number . ' updated with created_at: ' . $so->created_at);
            } else {
                $this->info('Sales order ' . $so->sales_order_number . ' has no parent sales order with draft number: ' . $ambilDraft);
            }
        }
    }
}
