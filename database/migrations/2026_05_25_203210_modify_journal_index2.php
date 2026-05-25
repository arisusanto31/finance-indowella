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
        Schema::table('journals', function ($table) {
            if(Schema::hasIndex('journals','journals_book_journal_id_code_group_index_date_unique')){
                $table->dropUnique('journals_book_journal_id_code_group_index_date_unique');
            }
            $table->unique([
                'book_journal_id',
                'code_group',
                'index_date'
            ],'journals_book_journal_id_code_group_index_date_unique');
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
