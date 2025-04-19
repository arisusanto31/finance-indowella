<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Contracts\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            UserTableSeeder::class,
            BookJournalSeeder::class,
            ChartAccountSeeder::class,
            SupplierSeeder::class,
            OtherPersonSeeder::class,
            CustomerSeeder::class,
            StockSeeder::class,
            PermissionSeeder::class,
        ]);
    }
}
