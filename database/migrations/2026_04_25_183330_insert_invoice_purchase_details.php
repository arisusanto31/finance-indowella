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
            $table->decimal('index_date',15,0)->nullable()->unique();
            $table->decimal('index_date_group',12)->nullable();
            $table->index(['index_date_group','index_date'],'idx_index_date_group');
            $table->integer('kartu_stock_id')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('invoice_purchase_details',function($table){
            $table->dropIndex('idx_index_date_group');
            $table->dropColumn('index_date');
            $table->dropColumn('index_date_group');
            $table->dropColumn('kartu_stock_id');
        });
    }
};
