<?php

namespace App\Jobs;

use App\Http\Controllers\SalesOrderController;
use App\Models\BackgroundProcess;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ImportSalesJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    protected $request, $bookID, $bgid;
    public function __construct($bookID, $request, $bgid)
    {
        //
        $this->bookID = $bookID;
        $this->request = $request;
        $this->bgid = $bgid;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        Session::put('book_journal_id',$this->bookID);
        $dataRequest = json_decode($this->request,true);
        $bg = BackgroundProcess::find($this->bgid);
        $st = SalesOrderController::store(new Request($dataRequest));
        if ($st['status'] == 1) {
            $bg->success();
        } else {
            $bg->failed();
            info("Import sales order failed for book ID {$this->bookID}. Status: " . json_encode($st));
        }
    }
}
