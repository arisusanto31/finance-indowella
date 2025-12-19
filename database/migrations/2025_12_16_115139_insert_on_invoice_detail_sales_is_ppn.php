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

        Schema::table('sales_orders',function($table){
            $table->boolean('is_ppn')->default(0)->after('total_price');
        });

        Schema::table('sales_order_details',function($table){
            $table->boolean('is_ppn')->default(0)->after('total_price');
        });

        Schema::table('invoice_sale_details',function($table){
            $table->boolean('is_ppn')->default(0)->after('total_price');
        });

        Schema::table('invoice_purchase_details',function($table){
            $table->boolean('is_ppn')->default(0)->after('total_price');
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
