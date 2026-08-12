<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;

class MakeExportDataJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    protected $month, $year, $bookid, $singkat, $force;
    public function __construct($month, $year, $bookid, $singkat, $force)
    {
        $this->month = $month;
        $this->year = $year;
        $this->bookid = $bookid;
        $this->singkat = $singkat;
        $this->force = $force;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        Artisan::call('make:export-data', [
            'month' => $this->month,
            'year' => $this->year,
            'bookid' => $this->bookid,
            'singkat' => $this->singkat,
            'force' => $this->force
        ]);
    }
}
