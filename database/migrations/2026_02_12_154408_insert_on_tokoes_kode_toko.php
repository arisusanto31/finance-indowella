<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        Schema::table('tokoes', function ($table) {
            $table->string('kode_toko')->nullable();
            $table->unique(['book_journal_id', 'kode_toko'], 'tokoes_book_journal_id_kode_toko_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('tokoes', function ($table) {
            $table->dropUnique('tokoes_book_journal_id_kode_toko_unique');
            $table->dropColumn('kode_toko');

        });
    }
};
