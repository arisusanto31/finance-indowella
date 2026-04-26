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

        Schema::table('invoice_purchase_details',function($table){
            $table->decimal('index_date_group', 12, 0)->change();
        });
        Schema::table('invoice_sale_details',function($table){
            $table->decimal('index_date_group', 12, 0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_purchase_details',function($table){
            $table->integer('index_date_group')->change();
        });
        Schema::table('invoice_sale_details',function($table){
            $table->integer('index_date_group')->change();
        });
    }
};
