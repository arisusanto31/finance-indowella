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
        Schema::table('invoice_packs', function (Blueprint $table) {
            $table->dropUnique('invoice_packs_invoice_number_unique');
            $table->unique(['book_journal_id', 'invoice_number'],'invoice_packs_book_journal_id_invoice_number_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('invoice_packs', function (Blueprint $table) {
            $table->dropUnique('invoice_packs_book_journal_id_invoice_number_unique');
            $table->unique('invoice_number');
        });
    }
};
