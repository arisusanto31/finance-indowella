<?php

namespace Database\Seeders;

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
        DB::table('chart_accounts')->insert($data);
    }
}
