<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BookJournalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        //
        DB::table('book_journals')->insert([
            [
                'name' => 'Buku Manufaktur',
                'description' => 'pembukuan untuk manufaktur',
                'type' => 'manuf',
                'theme'=>'theme-default-blue.css'
            ],
            [
                'name' => 'Buku Toko',
                'description' => 'pembukuan untuk toko retail',
                'type' => 'retail',
                'theme'=>'theme-default-green.css'
            ],
        ]);
    }
}
