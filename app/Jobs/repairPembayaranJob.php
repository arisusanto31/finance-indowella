<?php

namespace App\Jobs;

use App\Http\Controllers\JournalController;
use App\Models\BackgroundProcess;
use App\Models\InvoicePack;
use App\Models\Journal;
use App\Models\SalesOrder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Session;

class repairPembayaranJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    protected $bookid;
    protected $id;
    protected $bgProcessID;
    public function __construct($bookid, $id, $bgProcessID)
    {
        $this->bookid = $bookid;
        $this->id = $id;
        $this->bgProcessID = $bgProcessID;
    }

    /**
     * Execute the job.
     */


    public function handle(): void
    {
        //

        try {
            Session::put('book_journal_id', $this->bookid);
            $saleOrder = SalesOrder::find($this->id);
            $st=$saleOrder->repairPembayaran();
            if($st){
                $this->success();
            }
            else{
                $this->failed();
            }
            // $invoice = InvoicePack::where('sales_order_id', $saleOrder->id)->first();
            // if (!$invoice) {
            //     throw new \Exception('Invoice tidak ditemukan untuk sales order id ' . $saleOrder->id);
            // }
            // $journal = Journal::where('description', 'pelunasan piutang dari invoice ' . $invoice->invoice_number)->first();
            // if ($journal) {
            //     $st = JournalController::destroy($journal->id, 1);
            //     if ($st['status'] == 1) {
            //         info('Pembayaran invoice ' . $invoice->invoice_number . ' berhasil dibatalkan');
            //     } else {

            //         throw new \Exception('Gagal membatalkan pembayaran invoice ' . $invoice->invoice_number . '
            // Error: ' . $st['msg']);
            //     }
            // }

            // $st = $saleOrder->lunaskanDagang();
            // if ($st['status'] == 1) {
            //     info('Status pelunasan untuk sales order ' . $saleOrder->sales_order_number . ' berhasil diupdate');
            //     $this->success();
            // } else {
            //     info('Gagal mengupdate status pelunasan untuk sales order ' . $saleOrder->sales_order_number . '
            // Error: ' . $st['msg']);
            // $this->failed();
            // }
        } catch (\Exception $e) {
            info('Error processing sales order ' . $saleOrder->sales_order_number . ': ' . $e->getMessage());
            $this->failed();
        }
    }

    public function success(): void
    {
        $backgroundProcess = BackgroundProcess::find($this->bgProcessID);
        $backgroundProcess->success_task = $backgroundProcess->success_task + 1;
        $backgroundProcess->progress = (($backgroundProcess->success_task + $backgroundProcess->failed_task) / $backgroundProcess->total_task) * 100;

        if ($backgroundProcess->progress >= 100) {
            $backgroundProcess->status = 'finished';
        }
        $backgroundProcess->save();
    }

    public function failed(): void
    {
        $backgroundProcess = BackgroundProcess::find($this->bgProcessID);
        $backgroundProcess->failed_task = $backgroundProcess->failed_task + 1;
        $backgroundProcess->progress = (($backgroundProcess->success_task + $backgroundProcess->failed_task) / $backgroundProcess->total_task) * 100;
        if ($backgroundProcess->progress >= 100) {
            $backgroundProcess->status = 'finished';
        }
        $backgroundProcess->save();
    }
}
