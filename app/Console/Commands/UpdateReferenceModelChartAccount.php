<?php

namespace App\Console\Commands;

use App\Models\ChartAccount;
use Illuminate\Console\Command;

class UpdateReferenceModelChartAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-reference-model-chart-account';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $json = file_get_contents(public_path('chart_account.json'));

        // 2. Decode jadi array
        $data = json_decode($json, true);

        // 3. Insert ke database
        // DB::table('chart_accounts')->insert($data)
        foreach ($data as $d) {
            $ca = ChartAccount::find($d['id']);
            if ($ca) {
                $ca->reference_model = $d['reference_model'];
                $ca->save();
                $this->info("Chart account with id {$d['id']} updated");
            } else {
                $this->error("Chart account with id {$d['id']} not found");
                ChartAccount::create($d);
                $this->info("Chart account with id {$d['id']} created");
            }
        }
    }
}
