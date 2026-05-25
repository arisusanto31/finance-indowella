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
        Schema::table('journals',function($table){
            if(Schema::hasIndex('journals','journals_book_journal_id_index_date_chart_account_id_unique')){
                $table->dropUnique('journals_book_journal_id_index_date_chart_account_id_unique');
            }
            $table->dropIndex('journal_chart_account_date_group_index');
            $table->unique([
                'book_journal_id',
                'index_date',
                'code_group'
            ]);
            $table->index([
                'book_journal_id',
                'code_group',
                'index_date_group'
            ]);
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
