<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OtherPersonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('other_persons')->insert([
            [
                'name' => 'UMU SHOLICHATI',
                'phone' => '23451234',
                'address' => 'jalan kedamaian jalan surga',
            ],
            [
                'name' => 'M SHOLEH',
                'phone' => '09761234',
                'address' => 'jalan istiqomah tanpa putus',
            ],
        ]);
    }
}
