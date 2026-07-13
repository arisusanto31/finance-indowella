<?php

namespace App\Console\Commands;

use App\Models\SalesOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class BatalkanProsesSalesOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batalkan:proses-sales-order {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Batalkan proses sales order berdasarkan ID';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //itunitun
        $sales = DB::table('sales_orders')->where('id', $this->argument('id'))->first();
        Session::put('book_journal_id', $sales->book_journal_id);
        $sales= SalesOrder::find($this->argument('id'));
        if(!$sales){
            $this->error('Sales order tidak ditemukan.');
            return;
        }
        $sales->removeAllProcess();
        $this->info('Proses sales order berhasil dibatalkan.');
    }
}
