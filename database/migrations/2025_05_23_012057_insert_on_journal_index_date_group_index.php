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
            $table->decimal('index_date_group', 12, 0)->after('index_date')->nullable();
            $table->index(['book_journal_id', 'chart_account_id', 'index_date_group'], 'journal_chart_account_date_group_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('journals', function (Blueprint $table) {
            $table->dropColumn('index_date_group');
            $table->dropIndex('journal_chart_account_date_group_index');
        });
    }
};
