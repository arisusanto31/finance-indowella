<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('suppliers')->insert([
            [
                'name' => 'JOYOBOYO',
                'phone' => '123456789',
                'address' => 'jalan banyak tikungan',
            ],
            [
                'name' => 'PANCA BUDI',
                'phone' => '987654321',
                'address' => 'jalan terus tapi gak pernah jadian',
            ],
        ]);
    }
}
