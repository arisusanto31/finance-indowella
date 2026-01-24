<?php

namespace App\Jobs;

use App\Http\Controllers\KartuStockController;
use App\Http\Controllers\StockController;
use App\Models\KartuStock;
use App\Models\ManufStock;
use App\Models\RetailStock;
use App\Models\Stock;
use App\Models\StockCategory;
use App\Models\StockUnit;
use App\Models\TaskImportDetail;
use App\Services\ContextService;
use CustomLogger;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ImportKartuStockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * Create a new job instance.
     */
    protected $taskID;
    public function __construct($taskID)
    {
        //
        $this->taskID = $taskID;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        CustomLogger::log('journal', 'info', 'ImportKartuStockJob-' . $this->taskID);
        KartuStockController::processTaskImport($this->taskID);
    }
}
