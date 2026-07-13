<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CekProblemPenjualan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cek-problem-penjualan';

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

        $problems=DB::select('select s.total_price as tsum,sum(dk.amount_kredit) as tlink,s.sales_order_number from sales_orders as s left join detail_kartu_invoices as dk on dk.sales_order_number=s.sales_order_number AND dk.account_code_group=401000 where month(s.created_at)=1  and year(s.created_at)=2026 and s.book_journal_id=2 group by s.id having tsum <> tlink');
    }
}
