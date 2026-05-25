<?php

namespace App\Console\Commands;

use App\Http\Controllers\SalesOrderController;
use App\Jobs\InvoicingProcessJob;
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
     * @var strinC
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
        InvoicingProcessJob::dispatch($bookid, $month)->onQueue('default');
      
    }
}
