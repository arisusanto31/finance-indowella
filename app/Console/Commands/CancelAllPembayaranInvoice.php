<?php

namespace App\Console\Commands;

use App\Http\Controllers\JournalController;
use App\Models\InvoicePack;
use App\Models\Journal;
use App\Models\SalesOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;

class CancelAllPembayaranInvoice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cancel-all-pembayaran-invoice {bookid} {month} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel all pembayaran invoice for a given ID';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $bookid = $this->argument('bookid');
        Session::put('book_journal_id', $bookid);

        $month = $this->argument('month');
        $start = createCarbon($month)->startOfMonth();
        $end = createCarbon($month)->endOfMonth();
        $salesOrders = SalesOrder::whereBetween('created_at', [$start, $end])->get();
        foreach ($salesOrders as $saleOrder) {
            try {
                $invoice = InvoicePack::where('sales_order_id', $saleOrder->id)->first();
                if (!$invoice) {
                    throw new \Exception('Invoice tidak ditemukan untuk sales order id ' . $saleOrder->id);
                }


                $journal = Journal::where('description', 'pelunasan piutang dari invoice ' . $invoice->invoice_number)->first();
                if ($journal) {

                    $st = JournalController::destroy($journal->id, 1);
                    if ($st['status'] == 1) {
                        $this->info('Pembayaran invoice ' . $invoice->invoice_number . ' berhasil dibatalkan');
                    } else {

                        throw new \Exception('Gagal membatalkan pembayaran invoice ' . $invoice->invoice_number . '
            Error: ' . $st['msg']);
                    }
                }


                $st = $saleOrder->lunaskanDagang();
                if ($st['status'] == 1) {
                    $this->info('Status pelunasan untuk sales order ' . $saleOrder->sales_order_number . ' berhasil diupdate');
                } else {
                    $this->error('Gagal mengupdate status pelunasan untuk sales order ' . $saleOrder->sales_order_number . '
            Error: ' . $st['msg']);
                }
            } catch (\Exception $e) {
                $this->error('Error processing sales order ' . $saleOrder->sales_order_number . ': ' . $e->getMessage());
            }
        }
    }
}
