<?php

namespace App\Console\Commands;

use App\Models\InvoiceSaleDetail;
use App\Models\SalesOrderDetail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class FixMismatchInvoicePenjualan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:mismatch-invoice-penjualan {bookid}';

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
        $bookId = $this->argument('bookid');
        Session::put('book_journal_id', $bookId);
        $query = DB::select('select sum(inv.total_price) as price_invoice, sum(jk.amount_journal) as price_journal, inv.id,inv.sales_order_id,group_concat(inv.invoice_pack_number) as numbers from invoice_sale_details as inv left join detail_kartu_invoices as jk on jk.kartu_id= inv.id AND jk.kartu_type="App\\\\Models\\\\InvoiceSaleDetail" where inv.book_journal_id =?  group by inv.id having price_invoice <> abs(price_journal)', [$bookId]);
        foreach ($query as $row) {
            $this->info("Invoice ID: {$row->id}, Invoice Numbers: {$row->numbers}, Price Invoice: {$row->price_invoice}, Price Journal: {$row->price_journal}");
            // coba cari cara simpel dulu ya
            $invd = InvoiceSaleDetail::find($row->id);
            $sod = SalesOrderDetail::find($invd->sales_order_id);
            $invd->total_price = $invd->quantity/ $sod->qtyjadi * $sod->total_price;
            if ($invd->total_price == abs($row->price_journal)) {
                $invd->save();
                $this->info("Updated Invoice ID: {$invd->id}, New Total Price: {$invd->total_price}");
            } else {
                $this->error("Invoice ID: {$invd->id} could not be fixed. Total Price: {$invd->total_price}, Price Journal: {$row->price_journal}");
            }
        }
        $this->info("Finished fixing mismatched invoices.");
    }
}
