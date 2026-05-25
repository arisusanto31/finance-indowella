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
            if (Schema::hasIndex('journals', 'journals_book_journal_id_index_date_code_group_unique')) {
                $table->dropUnique('journals_book_journal_id_index_date_code_group_unique');
            }
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
