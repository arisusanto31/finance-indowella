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

        Schema::table('invoice_sale_details',function($table){
            $table->dropUnique('invoice_sale_details_index_date_unique');
            $table->unique(['book_journal_id','index_date'],'invoice_sale_details_book_journal_id_index_date_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_sale_details',function($table){
            $table->dropUnique('invoice_sale_details_book_journal_id_index_date_unique');
            $table->unique('index_date','invoice_sale_details_index_date_unique');
        });
    }
};
