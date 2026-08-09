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

        Schema::table('invoice_sale_details', function ($table) {
            $table->index('sales_order_id', 'idx_sales_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('invoice_sale_details', function ($table) {
            $table->dropIndex('idx_sales_order_id');
        });
    }
};
