<?php

namespace App\Console\Commands;

use App\Models\SalesOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CekSalesNotReady extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cek:sales-not-ready {bookid} {monthyear}';

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
        Session::put('book_journal_id', $this->argument('bookid'));
        $monthyear = $this->argument('monthyear') . '-01';
        $startDate = createCarbon($monthyear)->startOfMonth();
        $endDate = createCarbon($monthyear)->endOfMonth();
        $salesOrders = SalesOrder::whereBetween('created_at', [$startDate, $endDate])
            ->where('status_delivery', '<>', 'TERKIRIM 100%')->select('id', 'sales_order_number','book_journal_id');

        $salesOrders = SalesOrder::fromSub($salesOrders, 'sales_orders')->leftJoin('sales_order_details', 'sales_orders.id', '=', 'sales_order_details.sales_order_id')
            ->select('sales_orders.id', 'sales_orders.sales_order_number', DB::raw('count(sales_order_details.id) as total_detail'))
            ->groupBy('sales_orders.id')
            ->havingRaw('total_detail > 15')
            ->get();
        tampilkanTableTerminal(
            $salesOrders,
            [
                'id' => 'center',
                'sales_order_number' => 'left',
                'total_detail' => 'center'
            ],
            $this
        );

        if(!$this->confirm('yakin mau melanjutkan ? ')){
            $this->info('Proses dibatalkan');
            return;
        }
        foreach($salesOrders as $so) {
            $this->call('split:nota-command', [
                'salesid' => $so->id,
                'autonota' => 4
            ]);
        }
    }
}
