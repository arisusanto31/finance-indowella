<?php

namespace App\Console\Commands;

use App\Models\BackgroundProcess;
use App\Models\SalesOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;

class UpdateSalesStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:sales-status {bookid} {mothyear}';

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
        $bookid=$this->argument('bookid');

        $monthyear=$this->argument('mothyear');
        Session::put('book_journal_id', $bookid);
        $dateAwal=createCarbon($monthyear.'-01')->startOfMonth()->format('Y-m-d H:i:s');
        $dateAkhir=createCarbon($monthyear.'-01')->endOfMonth()->format('Y-m-d H:i:s');
        $sales= SalesOrder::whereBetween('created_at',[$dateAwal,$dateAkhir])->get();
        $bgProcess= BackgroundProcess::make($bookid,'admin/invoice/sales-order','update sales status',count($sales));
        foreach($sales as $sale){
            $sale->updateStatus();
            $bgProcess->success();
            $this->info('update status sales order '.$sale->sales_order_number.' to '.$sale->status);
        }
    }
}
