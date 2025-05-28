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
            $table->unique('index_date');
        });

        Schema::table('kartu_prepaid_expenses', function ($table) {
            $table->unique('index_date');
        });

        Schema::table('kartu_piutangs', function ($table) {
            $table->unique('index_date');
        });

        Schema::table('kartu_hutangs', function ($table) {
            $table->unique('index_date');
        });
        Schema::table('kartu_inventories', function ($table) {
            $table->unique('index_date');
        });
        Schema::table('kartu_dp_sales', function ($table) {
            $table->unique('index_date');
        });
        Schema::table('kartu_bdps', function ($table) {
            $table->unique('index_date');
        });
        Schema::table('kartu_bahan_jadis', function ($table) {
            $table->unique('index_date');
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
