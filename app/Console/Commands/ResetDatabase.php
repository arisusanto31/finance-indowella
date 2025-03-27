<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class ResetDatabase extends Command
{
    protected $signature = 'db:reset';
    protected $description = 'Drop all tables, run migrations, and seed the database';

    public function handle()
    {
        $this->warn('⚠️ Semua tabel akan dihapus...');
        if (!$this->confirm('Apakah kamu yakin ingin menghapus semua tabel?')) {
            $this->info('Aksi dibatalkan.');
            return 0;
        }

        // Disable foreign key checks
        Schema::disableForeignKeyConstraints();

        // Get all tables
        $tables = DB::select('SHOW TABLES');
        $dbName = env('DB_DATABASE');
        if (empty($tables)) {
            $this->info('⚠️ Tidak ada tabel yang ditemukan.');
            return;
        }

        // Ambil nama key pertama (karena MariaDB pakai format `Tables_in_nama_database`)
        $key = array_keys((array)$tables[0])[0];


        foreach ($tables as $table) {
            $tableName = $table->$key;
            Schema::drop($tableName);
            $this->line("Dropped table: {$tableName}");
        }

        Schema::enableForeignKeyConstraints();

        $this->info('✅ Semua tabel berhasil dihapus.');

        // Run migrations
        $this->call('migrate');

        // Run seeders
        $this->call('db:seed');

        $this->info('✅ Migrasi dan seeder selesai.');
        return 0;
    }
}
