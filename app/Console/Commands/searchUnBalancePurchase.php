<?php

namespace App\Console\Commands;

use App\Models\InvoicePurchaseDetail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class searchUnBalancePurchase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search:unbalance-purchase';

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
        //3
        $purchases = InvoicePurchaseDetail::from('invoice_purchase_details as p')
            ->leftJoin('journals as j', 'p.journal_id', '=', 'j.id')
            ->leftJoin('kartu_stocks as ks', 'p.kartu_stock_id', '=', 'ks.id')
            ->select(
                'p.id as purchasing_id',
                DB::raw('group_concat(p.total_price)'),
                'j.id as journal_id',
                'j.amount_debet',
                'ks.id as kartu_stock_id',
                DB::raw('group_concat(ks.mutasi_rupiah_total)'),
                DB::raw('sum(total_price) as sum_total_price'),
                DB::raw('sum(mutasi_rupiah_total) as sum_mutasi_rupiah')
            )
            // ->whereRaw(
            // 'total_price != amount_debet or total_price != mutasi_rupiah_total'
            // )
            ->groupBy('j.id')
            ->havingRaw('sum_total_price!= amount_debet or sum_total_price != sum_mutasi_rupiah')
            ->get();

        tampilkanTableTerminal(
            $purchases,
            [
                'purchasing_id' => 'center',
                'total_price' => 'right',
                'journal_id' => 'center',
                'amount_debet' => 'right',
                'kartu_stock_id' => 'center',
                'total_kartu' => 'right'
            ],
            $this
        );
    }
}
