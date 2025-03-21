<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TruncateTables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:truncate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'kosongkan semua table lur. ';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $tables = DB::select('SHOW TABLES');

        if (empty($tables)) {
            $this->info('⚠️ Tidak ada tabel yang ditemukan.');
            return;
        }

        // Ambil nama key pertama (karena MariaDB pakai format `Tables_in_nama_database`)
        $key = array_keys((array)$tables[0])[0];

        foreach ($tables as $table) {
            $tableName = $table->$key; // Ambil nama tabel dari kolom yang benar

            if ($tableName === 'migrations') continue; // Jangan hapus tabel migrations

            DB::table($tableName)->truncate();
            $this->info("✅ Truncated: {$tableName}");
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info('✅ Semua tabel berhasil dikosongkan.');
    }
}
