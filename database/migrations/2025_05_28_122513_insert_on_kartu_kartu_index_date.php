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
        Schema::table('kartu_stocks', function ($table) {
            $table->decimal('index_date', 15, 0)->after('id')->default(0);
            $table->decimal('index_date_group', 12, 0)->after('index_date')->default(0);
            $table->index(['book_journal_id', 'index_date_group', 'index_date'], 'kartu_stocks_index_date_group_index_date');
        });

        Schema::table('kartu_prepaid_expenses', function ($table) {
            $table->decimal('index_date', 15, 0)->after('id')->default(0);
            $table->decimal('index_date_group', 12, 0)->after('index_date')->default(0);
            $table->index(['book_journal_id', 'index_date_group', 'index_date'], 'kartu_stocks_index_date_group_index_date');
        });

        Schema::table('kartu_piutangs', function ($table) {
            $table->decimal('index_date', 15, 0)->after('id')->default(0);
            $table->decimal('index_date_group', 12, 0)->after('index_date')->default(0);
            $table->index(['book_journal_id', 'index_date_group', 'index_date'], 'kartu_stocks_index_date_group_index_date');
        });

        Schema::table('kartu_hutangs', function ($table) {
            $table->decimal('index_date', 15, 0)->after('id')->default(0);
            $table->decimal('index_date_group', 12, 0)->after('index_date')->default(0);
            $table->index(['book_journal_id', 'index_date_group', 'index_date'], 'kartu_stocks_index_date_group_index_date');
        });
        Schema::table('kartu_inventories', function ($table) {
            $table->decimal('index_date', 15, 0)->after('id')->default(0);
            $table->decimal('index_date_group', 12, 0)->after('index_date')->default(0);
            $table->index(['book_journal_id', 'index_date_group', 'index_date'], 'kartu_stocks_index_date_group_index_date');
        });
        Schema::table('kartu_dp_sales', function ($table) {
            $table->decimal('index_date', 15, 0)->after('id')->default(0);
            $table->decimal('index_date_group', 12, 0)->after('index_date')->default(0);
            $table->index(['book_journal_id', 'index_date_group', 'index_date'], 'kartu_stocks_index_date_group_index_date');
        });
        Schema::table('kartu_bdps', function ($table) {
            $table->decimal('index_date', 15, 0)->after('id')->default(0);
            $table->decimal('index_date_group', 12, 0)->after('index_date')->default(0);
            $table->index(['book_journal_id', 'index_date_group', 'index_date'], 'kartu_stocks_index_date_group_index_date');
        });
        Schema::table('kartu_bahan_jadis', function ($table) {
            $table->decimal('index_date', 15, 0)->after('id')->default(0);
            $table->decimal('index_date_group', 12, 0)->after('index_date')->default(0);
            $table->index(['book_journal_id', 'index_date_group', 'index_date'], 'kartu_stocks_index_date_group_index_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('kartu_stocks', function (Blueprint $table) {
            $table->dropIndex('kartu_stocks_index_date_group_index_date');
            $table->dropColumn(['index_date', 'index_date_group']);
        });
        Schema::table('kartu_prepaid_expenses', function (Blueprint $table) {
            $table->dropIndex('kartu_stocks_index_date_group_index_date');
            $table->dropColumn(['index_date', 'index_date_group']);
        });
        Schema::table('kartu_piutangs', function (Blueprint $table) {
            $table->dropIndex('kartu_stocks_index_date_group_index_date');
            $table->dropColumn(['index_date', 'index_date_group']);
        });
        Schema::table('kartu_hutangs', function (Blueprint $table) {
            $table->dropIndex('kartu_stocks_index_date_group_index_date');
            $table->dropColumn(['index_date', 'index_date_group']);
        });
        Schema::table('kartu_inventories', function (Blueprint $table) {
            $table->dropIndex('kartu_stocks_index_date_group_index_date');
            $table->dropColumn(['index_date', 'index_date_group']);
        });
        Schema::table('kartu_dp_sales', function (Blueprint $table) {
            $table->dropIndex('kartu_stocks_index_date_group_index_date');
            $table->dropColumn(['index_date', 'index_date_group']);
        });
        Schema::table('kartu_bdps', function (Blueprint $table) {
            $table->dropIndex('kartu_stocks_index_date_group_index_date');
            $table->dropColumn(['index_date', 'index_date_group']);
        });
        Schema::table('kartu_bahan_jadis', function (Blueprint $table) {
            $table->dropIndex('kartu_stocks_index_date_group_index_date');
            $table->dropColumn(['index_date', 'index_date_group']);
        });
    }
};
