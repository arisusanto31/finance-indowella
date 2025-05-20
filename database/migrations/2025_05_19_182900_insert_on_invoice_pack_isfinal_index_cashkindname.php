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
        Schema::table('invoice_packs', function ($table) {
            $table->boolean('is_final')->defaul(0)->after('status');
            $table->string('ref_akun_cash_kind_name')->nullable();
            $table->integer('index')->default(0);
            $table->index(['book_journal_id', 'is_final', 'person_id', 'person_type', 'index'], 'invoice_final_customer_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('invoice_packs', function (Blueprint $table) {
            $table->dropColumn('is_final');
            $table->dropColumn('ref_akun_cash_kind_name');
            $table->dropColumn('index');
            $table->dropIndex('invoice_final_customer_index');
        });
    }
};
