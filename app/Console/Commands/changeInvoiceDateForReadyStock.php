<?php

namespace App\Console\Commands;

use App\Models\KartuStock;
use App\Models\SalesOrder;
use App\Models\StockUnit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;

class changeInvoiceDateForReadyStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'change:invoice-date-for-ready-stock {bookid} {monthyear}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change invoice date for ready stock';

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
        $salesOrders = SalesOrder::where('created_at', '>=', $startDate)
            ->where('created_at', '<', $endDate)->where('is_ready_stock', 0)->get();

        $allConversion = StockUnit::select('stock_id','konversi','unit')->get()->groupBy('stock_id')->map(function ($q) {
            return $q->pluck('konversi', 'unit')->toArray();
        });
        foreach ($salesOrders as $so) {
            $so->updateReadyStock();
            if ($so->is_ready_stock == 0) {
                $st = $so->findDateReadyStock($allConversion);
                $this->info($st['msg']);
            }
        }
    }
}
