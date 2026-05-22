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
        Schema::table('kartu_stocks',function($table){
            $table->dropIndex('kartu_stocks_index_date_unique');
            $table->unique(['book_journal_id','index_date'],'kartu_stocks_index_date_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('kartu_stocks',function($table){
            $table->dropUnique('kartu_stocks_index_date_unique');
            $table->unique(['index_date'],'kartu_stocks_index_date_unique');
        });
    }
};
