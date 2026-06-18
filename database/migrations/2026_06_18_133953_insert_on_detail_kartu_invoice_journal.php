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
        Schema::table('detail_kartu_invoices', function ($table) {
            $table->index(['journal_id', 'book_journal_id'], 'idx_journal_book_journal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_kartu_invoices', function ($table) {
            $table->dropIndex('idx_journal_book_journal');
        });
    }
};
