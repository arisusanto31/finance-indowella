<?php

namespace App\Console\Commands;

use App\Models\DetailKartuInvoice;
use App\Models\InvoiceSaleDetail;
use App\Models\SalesOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;

class RepairLinkPPNKeluaran extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repair:link-ppn-keluaran {bookid}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Repair link PPN Keluaran';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        Session::put('book_journal_id', $this->argument('bookid'));
        $keluaran = DetailKartuInvoice::from('detail_kartu_invoices as di')
            ->join('invoice_packs as ip', 'ip.id', '=', 'di.invoice_pack_id')
            ->where('ip.reference_model', InvoiceSaleDetail::class)
            ->whereNull('ip.sales_order_id')
            ->select('di.*', 'ip.sales_order_id as real_sales_order_id')
            ->get();
        foreach ($keluaran as $k) {
            $sales = SalesOrder::find($k->real_sales_order_id);
            if ($sales) {
                $dk = DetailKartuInvoice::find($k->id);
                $dk->sales_order_id = $sales->id;
                $dk->sales_order_number = $sales->sales_order_number;
                $dk->save();
                $this->info('repair link ppn keluaran ' . $k->id. ' sales '.$sales->sales_order_number);
            }
            $sales->updateStatus();
        }
    }
}
