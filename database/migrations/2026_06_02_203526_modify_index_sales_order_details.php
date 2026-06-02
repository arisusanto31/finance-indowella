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
        Schema::table('sales_order_details', function ($table) {
            $table->index('sales_order_number');
        });

        Schema::table('detail_kartu_invoices', function ($table) {
            $table->index('sales_order_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_order_details', function ($table) {
            $table->dropIndex(['sales_order_number']);
        });

        Schema::table('detail_kartu_invoices', function ($table) {
            $table->dropIndex(['sales_order_number']);
        });
    }
};
