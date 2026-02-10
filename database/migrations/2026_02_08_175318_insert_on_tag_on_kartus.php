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

        Schema::table('kartu_bahan_jadis', function (Blueprint $table) {
            $table->string('tag')->nullable()->after('id');
        });

        Schema::table('kartu_bdps', function ($table) {
            $table->string('tag')->nullable()->after('id');
        });

        Schema::table('kartu_dp_sales', function ($table) {
            $table->string('tag')->nullable()->after('id');
        });

        Schema::table('kartu_hutangs', function ($table) {
            $table->string('tag')->nullable()->after('id');
        });

        Schema::table('kartu_inventories', function ($table) {
            $table->string('tag')->nullable()->after('id');
        });

        Schema::table('kartu_piutangs', function ($table) {
            $table->string('tag')->nullable()->after('id');
        });

        Schema::table('kartu_prepaid_expenses', function ($table) {
            $table->string('tag')->nullable()->after('id');
        });

        Schema::table('kartu_stocks', function ($table) {
            $table->string('tag')->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('kartu_bahan_jadis', function (Blueprint $table) {
            $table->dropColumn('tag');
        });
    }
};
