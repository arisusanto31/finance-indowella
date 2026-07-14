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
            $table->decimal('journal_identifier',20,0)->nullable()->after('index_date');
            $table->unique(['journal_identifier','book_journal_id'],'journals_book_identifier_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('journals',function($table){
            $table->dropUnique('journals_book_identifier_unique');
            $table->dropColumn('journal_identifier');
        });
    }
};
