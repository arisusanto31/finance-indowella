<?php

namespace App\Jobs;

use App\Http\Controllers\SalesOrderController;
use App\Models\BackgroundProcess;
use App\Models\SalesOrder;
use CustomLogger;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class InvoicingProcessJob implements ShouldQueue
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
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        // $this->info("hallo brother");
        // return ;
        try {
            $bookid = $this->bookid;
            $id = $this->id;
            $bgProcessID = $this->bgProcessID;
            Session::put('book_journal_id', $bookid);
            $saleOrder = SalesOrder::find($id);
            if (!$saleOrder) {
                $this->info("Sales order with ID $id not found. Exiting job.");
                return;
            }
            if ($saleOrder->is_final == 0) {
                $st = SalesOrderController::makeFinal(new Request([
                    'id' => $id
                ]));
                if ($st['status'] == 1) {
                    $this->info("Successfully marked sales order ID $id as final for book ID $bookid.");
                } else {
                    $this->info("Failed to mark sales order ID $id as final for book ID $bookid. Status: " . json_encode($st));
                    $lock = Cache::lock("update_bg_process_$bgProcessID", 10);
                    if ($lock->get()) {
                        $backgroundProcess = BackgroundProcess::find($bgProcessID);
                        $backgroundProcess->failed_task = $backgroundProcess->failed_task + 1;
                        $backgroundProcess->progress = (($backgroundProcess->success_task + $backgroundProcess->failed_task) / $backgroundProcess->total_task) * 100;
                        if ($backgroundProcess->progress >= 100) {
                            $backgroundProcess->status = 'finished';
                        }
                        $backgroundProcess->save();
                        $lock->release();
                    }
                    return;
                }
            }
            $st = SalesOrderController::processDagang(new Request([
                'id' => $id
            ]));
            if ($st['status'] == 1) {
              
                $lock = Cache::lock("update_bg_process_$bgProcessID", 10);
                if ($lock->get()) {
                    $backgroundProcess = BackgroundProcess::find($bgProcessID);
                    $backgroundProcess->success_task = $backgroundProcess->success_task + 1;
                    $backgroundProcess->progress = (($backgroundProcess->success_task + $backgroundProcess->failed_task) / $backgroundProcess->total_task) * 100;

                    if ($backgroundProcess->progress >= 100) {
                        $backgroundProcess->status = 'finished';
                    }
                    $backgroundProcess->save();
                      info("Successfully processed sales order ID $id for book ID $bookid.");
                    $lock->release();
                }
            } else {
                $lock = Cache::lock("update_bg_process_$bgProcessID", 10);
                if ($lock->get()) {
                    $backgroundProcess = BackgroundProcess::find($bgProcessID);
                    $backgroundProcess->failed_task = $backgroundProcess->failed_task + 1;
                    $backgroundProcess->progress = (($backgroundProcess->success_task + $backgroundProcess->failed_task) / $backgroundProcess->total_task) * 100;
                    if ($backgroundProcess->progress >= 100) {
                        $backgroundProcess->status = 'finished';
                    }
                    $backgroundProcess->save();
                    info("Failed to process sales order ID $id for book ID $bookid. Status: " . json_encode($st));
                    $lock->release();
                }

                $this->info("Failed to process sales order ID $id for book ID $bookid. Status: " . json_encode($st));
            }
        } catch (\Exception $e) {
            $this->info('error on processing invoicing job for sales order ID ' . $this->id . ' and book ID ' . $this->bookid . '. Error: ' . $e->getMessage());
            info('error on processing invoicing job for sales order ID ' . $this->id . ' and book ID ' . $this->bookid . '. Error: ' . $e->getMessage());
        }
    }

    function info($message)
    {
        CustomLogger::log('background_process', 'info', "InvoicingProcessJob: $message");
    }
}
