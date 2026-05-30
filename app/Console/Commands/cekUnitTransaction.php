<?php

namespace App\Console\Commands;

use App\Models\SalesOrder;
use App\Models\SalesOrderDetail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;

class cekUnitTransaction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cek:unit-transaction {bookid} {monthyear}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check unit transactions for a specific book and month';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $bookID= $this->argument('bookid');
        $monthyear = $this->argument('monthyear') . '-01';
        Session::put('book_journal_id', $bookID);
        $startDate = createCarbon($monthyear)->startOfMonth();
        $endDate = createCarbon($monthyear)->endOfMonth();
        $sales= SalesOrderDetail::where('created_at', '>=', $startDate)
            ->where('created_at', '<', $endDate)
            ->where('unit','-')
            ->get();
        $allStockID= $sales->pluck('stock_id')->unique();
        $hpp= KartuStock::whereIn('index_date',function($q) use($allStockID){
            $q->selectRaw('max(index_date)')
                ->from('kartu_stocks')

                ->whereIn('stock_id',$allStockID)
                ->groupBy('stock_id');
        });
    }
}
