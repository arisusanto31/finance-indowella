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
        Schema::table('detail_kartu_invoices', function (Blueprint $table) {
            $table->datetime('date_journal')->nullable()->after('journal_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('detail_kartu_invoices', function (Blueprint $table) {
            $table->dropColumn('date_journal');
        });
    }
};
