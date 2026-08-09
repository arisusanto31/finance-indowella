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
        Schema::table('final_reports',function($table){
            $table->string('key_file')->nullable();
            $table->unique(['key_file','book_journal_id'],'idx_key_file_book_journal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('final_reports',function($table){
            $table->dropUnique('idx_key_file_book_journal');
            $table->dropColumn('key_file');
        });
    }
};
