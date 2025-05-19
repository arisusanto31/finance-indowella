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
        Schema::table('sales_orders', function ($table) {
            $table->boolean('is_final')->default(0)->after('status');
            $table->integer('index')->default(0);
            $table->index(['book_journal_id','is_final', 'customer_id', 'index'], 'so_final_customer_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn('is_final');
            $table->dropColumn('index');
            $table->dropIndex('so_final_customer_index');
        });
    }
};
