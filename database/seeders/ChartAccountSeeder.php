<?php

namespace Database\Seeders;

use App\Models\ChartAccount;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChartAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $json = file_get_contents(public_path('chart_account.json'));

        // 2. Decode jadi array
        $data = json_decode($json, true);

        // 3. Insert ke database
        // DB::table('chart_accounts')->insert($data)
        foreach ($data as $d) {
            if (!is_array($d)) {
                $this->command->error("-error data not array chart account " . json_encode($d));
            }
            $c = ChartAccount::create($d);
        }
    }
}
