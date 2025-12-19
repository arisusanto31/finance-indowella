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

        Schema::table('sales_order_details', function (Blueprint $table) {
            $table->decimal('total_ppn_k', 15, 2)->default(0)->after('total_price'); // ini langsung exclude dari total_price ya
        });
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->decimal('total_ppn_k', 15, 2)->default(0)->after('total_price');
        });
        Schema::table('invoice_sale_details', function (Blueprint $table) {
            $table->decimal('total_ppn_k', 15, 2)->default(0)->after('total_price');
        });
        Schema::table('invoice_purchase_details', function (Blueprint $table) {
            $table->decimal('total_ppn_m', 15, 2)->default(0)->after('total_price');
        });
        Schema::table('invoice_packs', function (Blueprint $table) {
            $table->decimal('total_ppn_k', 15, 2)->default(0)->after('total_price');
            $table->decimal('total_ppn_m', 15, 2)->default(0)->after('total_price');
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
