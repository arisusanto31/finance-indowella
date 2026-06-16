<?php

namespace App\Console\Commands;

use App\Jobs\repairPembayaranJob;
use App\Models\BackgroundProcess;
use App\Models\SalesOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;

class RepairPembayaran extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:repair-pembayaran {bookid} {month}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Repair pembayaran for a specific book and month';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $bookid = $this->argument('bookid');
        $month = $this->argument('month');

        // Your logic to repair pembayaran for the specific book and month
        Session::put('book_journal_id',$bookid);
        $startDate= createCarbon($month)->startOfMonth();
        $endDate= createCarbon($month)->endOfMonth();
        $salesOrders = SalesOrder::whereBetween('created_at', [$startDate, $endDate])
            ->where('status_payment','<>','LUNAS 100%')
            ->get();
        $totalTask = $salesOrders->count();
        $bgProses = BackgroundProcess::make($bookid, 'admin/invoice/sales-order', "Repair pembayaran for month: $month", $totalTask);
        foreach($salesOrders as $so){
            repairPembayaranJob::dispatch($bookid,$so->id,$bgProses->id)->onQueue('default');
        }
        info('Dispatched repair pembayaran jobs for ' . $totalTask . ' sales orders for month: ' . $month);
    }
}
