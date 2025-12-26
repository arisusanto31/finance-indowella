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
        Schema::table('suppliers', function (Blueprint $table) {
            // drop unique lama (name saja)
            $table->dropUnique('suppliers_name_unique');

            // buat unique baru (name + book_journal_id)
            $table->unique(['name', 'book_journal_id'], 'suppliers_name_book_journal_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
