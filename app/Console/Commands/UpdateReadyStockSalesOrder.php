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
    protected $signature = 'update:ready-stock-sales-order {bookid} {id}';

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
        $bookid = $this->argument('bookid');
        $id = $this->argument('id');
        Session::put('book_journal_id', $bookid);
        $SO = SalesOrder::find($id);
        $st=$SO->updateReadyStock();
        $this->info(json_encode($st));
        $this->info('ready stock sales order ' . $SO->is_ready_stock);
    }
}
