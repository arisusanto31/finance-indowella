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
            $table->dropUnique('sales_orders_sales_order_number_unique');
            $table->unique(['sales_order_number','book_journal_id'],'unique_number_book_journal_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_orders',function($table){
            $table->dropUnique('unique_number_book_journal_id');
            $table->unique('sales_order_number');
        });
    }
};
