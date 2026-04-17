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

        Schema::table('kartu_stocks', function (Blueprint $table) {
            $table->string('production_number')->nullable()->after('stock_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kartu_stocks', function (Blueprint $table) {
            $table->dropColumn('production_number');
        });
    }
};
