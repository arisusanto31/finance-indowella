<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TokoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $data = [
            [
                'book_journal_id' => 2,
                'name' => 'MANUFAKTUR1',
                'phone' => '08123456789',
                'address' => 'Jl. Raya No. 1',
            ],
        ];

        foreach ($data as $item) {
            \App\Models\Toko::create($item);
        }
    }
}
