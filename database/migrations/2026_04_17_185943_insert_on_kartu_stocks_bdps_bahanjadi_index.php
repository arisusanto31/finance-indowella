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
            $table->index(['production_number', 'stock_id'], 'idx_production_number_stock_id_index');
        });
        Schema::table('kartu_bdps', function ($table) {
            $table->index(['production_number', 'stock_id'], 'idx_production_number_stock_id_index');
        });

        Schema::table('kartu_bahan_jadis', function ($table) {
            $table->index(['production_number', 'stock_id'], 'idx_production_number_stock_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //

        Schema::table('kartu_stocks', function ($table) {
            $table->dropIndex('idx_production_number_stock_id_index');
        });
        Schema::table('kartu_bdps', function ($table) {
            $table->dropIndex('idx_production_number_stock_id_index');
        });
        Schema::table('kartu_bahan_jadis', function ($table) {
            $table->dropIndex('idx_production_number_stock_id_index');
        });
    }
};
