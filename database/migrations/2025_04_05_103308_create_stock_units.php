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
        Schema::create('stock_units', function (Blueprint $table) {
            $table->id();
            $table->integer('stock_id');
            $table->string('unit');
            $table->decimal('konversi', 8, 2)->nullable();
            // Tambahkan unique index untuk kombinasi stock_id dan unit
            $table->unique(['stock_id', 'unit']);
            $table->boolean('is_deleted')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_units');
    }
};
