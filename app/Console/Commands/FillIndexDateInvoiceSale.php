<?php

namespace App\Console\Commands;

use App\Models\InvoiceSaleDetail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class FillIndexDateInvoiceSale extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fill:index-date-invoice-sale';

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
        $books = DB::table('book_journals')->get();
        foreach ($books as $book) {
            Session::put('book_journal_id', $book->id);
            $invoiceSales = InvoiceSaleDetail::whereNull('index_date')->where('journal_id', '>', 0)->get();
            foreach ($invoiceSales as $inv) {
                $inv->fillIndexDate();
                $this->info('update index date invoice sale detail ' . $inv->id . ' to ' . $inv->index_date);
            }
        }
    }
}
