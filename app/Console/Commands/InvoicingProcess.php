<?php

namespace App\Console\Commands;

use App\Http\Controllers\SalesOrderController;
use App\Models\BackgroundProcess;
use App\Models\SalesOrder;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class InvoicingProcess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoicing:process {bookid} {month}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process invoicing for a given month and year';

    /**
     * Execute the console command.
     */

    public function handle()
    {
        //
        $bookid = $this->argument('bookid');
        $month = $this->argument('month');
        $date = createCarbon($month.'-'.'01');
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
        $this->info("Found $count sales order(s) to process for month: " . $date->format('F Y'));
        if ($count > 0) {
            $backgroundProcess = BackgroundProcess::create([
                'monitoring_url' => url('admin/invoice/sales-order'),
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
                    $this->info("Successfully processed sales order ID: {$sale->id}");
                } else {
                    $failedTask++;
                    $this->error("Failed to process sales order ID: {$sale->id}. Reason: " . $st['msg']);
                }
                if ($iProgress % 10 == 0 || $iProgress == $count) {
                    $this->info("Processed sales Progress: " . number_format(($iProgress / $count) * 100, 2) . "%");
                    $backgroundProcess->update([
                        'progress' => ($iProgress / $count) * 100,
                        'success_task' => $successTask,
                        'failed_task' => $failedTask,
                    ]);
                
                }
            }
        }
    }
}
