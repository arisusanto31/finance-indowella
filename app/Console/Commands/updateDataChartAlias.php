<?php

namespace App\Console\Commands;

use App\Models\ChartAccountAlias;
use Illuminate\Console\Command;

class updateDataChartAlias extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-chart-alias {bookid}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update chart account aliases with child status and levels';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $bookid = $this->argument('bookid');
        $this->info('book id: '.$bookid);
        $chartAlias = ChartAccountAlias::withoutGlobalScope('journal')->where('book_journal_id', $bookid)->get();
        $this->info("Found ".count($chartAlias)." chart account aliases to update.");
        foreach ($chartAlias as $alias) {
            $chart = $alias->chartAccount;
            if ($chart) {
                $alias->is_child = $chart->is_child;
                $alias->level = $chart->level;
                $alias->reference_model = $chart->reference_model;
                $alias->account_type = $chart->account_type;
                $alias->save();
                $this->info("Updated alias ID: {$alias->id}");
            }
        }
    }
}
