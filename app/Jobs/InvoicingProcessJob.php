<?php

namespace App\Jobs;

use App\Http\Controllers\SalesOrderController;
use App\Models\BackgroundProcess;
use App\Models\SalesOrder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class InvoicingProcessJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    protected $bookid;
    protected $month;
    public function __construct($bookid, $month)
     {
         $this->bookid = $bookid;
         $this->month = $month;

        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        $bookid = $this->bookid;
        $month = $this->month;  $date = createCarbon($month.'-'.'01');
        $startDate = $date->copy()->startOfMonth();
        $endDate = $date->copy()->endOfMonth();
        Session::put('book_journal_id', $bookid);
        $sales = SalesOrder::where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->where('is_final', 1)
            ->where('status_delivery', '<>', 'terkirim 100%')
            ->where('is_ready_stock', 1)
            ->get();
        $count = $sales->count();
        info("Found $count sales order(s) to process for month: " . $date->format('F Y'));
        if ($count > 0) {
            $backgroundProcess = BackgroundProcess::create([
                'monitoring_url' => 'admin/invoice/sales-order',
                'total_task' => $count,
                'description_process' => "Processing invoicing for month: $month and year: " . $date->year,
                'status' => 'processing',
                'progress' => 0,
            ]);
            $iProgress = 0;
            $successTask = 0;
            $failedTask = 0;
            foreach ($sales as $sale) {
                $st = SalesOrderController::processDagang(new Request(['id' => $sale->id]));
                $iProgress++;
                if ($st['status'] == 1) {
                    $successTask++;
                    info("Successfully processed sales order ID: {$sale->id}");
                } else {
                    $failedTask++;
                    info("Failed to process sales order ID: {$sale->id}. Reason: " . $st['msg']);
                }
                if ($iProgress % 10 == 0 || $iProgress == $count) {
                    info("Processed sales Progress: " . number_format(($iProgress / $count) * 100, 2) . "%");
                    $backgroundProcess->update([
                        'progress' => ($iProgress / $count) * 100,
                        'success_task' => $successTask,
                        'failed_task' => $failedTask,
                    ]);
                
                }
            }
            $backgroundProcess->update([
                'status' => 'finished',
                'progress' => 100,
                'success_task' => $successTask,
                'failed_task' => $failedTask,
            ]);
            info("Invoicing process completed. Total: $count, Success: $successTask, Failed: $failedTask");
        }

    }
}
