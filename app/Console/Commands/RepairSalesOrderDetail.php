<?php

namespace App\Console\Commands;

use App\Models\SalesOrderDetail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;

class RepairSalesOrderDetail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repair:sales-order-detail';

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
        Session::put('book_journal_id', 2);
        $details = SalesOrderDetail::where('sales_order_id', 0)->get();

        tampilkanTableTerminal(
            $details,
            [
                'id' => 'center',
                'sales_order_id' => 'center',
                'sales_order_number' => 'left',
                'custom_stock_name' => 'left',
                'quantity' => 'right',
                'unit' => 'left',
                'total_price' => 'right'
            ],
            $this
        );
        if (!$this->confirm('yakin mau melanjutkan ? ')) {
            $this->info('Proses dibatalkan');
            return;
        }
        // $details = $details->groupBy('sales_order_number')->map(function ($vals) {
        //     $totalPrice = $vals->sum('total_price');
        //     $totalPpn = $vals->sum('total_ppn_k');
        //     $tokoId = $vals->first()->toko_id;
        //     $customerId = $vals->first()->customer_id;
        //     $thesale = new SalesOrder();
        //     $thesale->toko_id = $sale->toko_id;
        //     $thesale->book_journal_id = bookID();
        //     $thesale->is_ppn = $sale->is_ppn;
        //     $thesale->total_ppn_k = $sale->total_ppn_k;
        //     $thesale->total_price = $sale->total_price;
        //     $thesale->ref_akun_cash_kind_name = $sale->ref_akun_cash_kind_name;
        //     $thesale->draft_number = $sale->draft_number . '-' . ($i + 2);
        //     $thesale->sales_order_number = $thesale->draft_number;
        //     $thesale->customer_id = $sale->customer_id;
        //     $thesale->save();
        //     $newsales[] = $thesale;
        // });
    }
}
