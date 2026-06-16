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

        Schema::table('link_reference_cash_kinds', function ($table) {
            $table->dropUnique('unique_cash_kind_code_group');
            $table->unique(['cash_kind_name', 'book_journal_id'], 'unique_cash_kind_code_group');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('link_reference_cash_kinds', function ($table) {
            $table->dropUnique('unique_cash_kind_code_group');
            $table->unique(['code_group', 'cash_kind_name', 'book_journal_id'], 'unique_cash_kind_code_group');
        });
    }
};
