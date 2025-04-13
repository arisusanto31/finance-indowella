<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $data = [
            [
                'name' => 'OYI',
                'address' => 'malang',
                'phone' => '08251234234',

            ],
            [
                'name' => 'Balibul',
                'address' => 'malang',
                'phone' => '08251234234',

            ],
            [
                'name' => 'Hotwing',
                'address' => 'malang',
                'phone' => '08251234234',
            ],
            [
                'name' => 'Red Chicken',
                'address' => 'malang',
                'phone' => '08251234234',

            ]
        ];
        foreach ($data as $d) {
            Customer::create($d);
        }
    }
}
