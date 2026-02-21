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
             $table->string('draft_number')->nullable()->after('sales_order_number');

        });;
        Schema::table('sales_order_details',function($table){
             $table->string('draft_number')->nullable()->after('sales_order_number');

         });;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
