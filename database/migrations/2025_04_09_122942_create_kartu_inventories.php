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
        Schema::create('kartu_inventories', function (Blueprint $table) {
            $table->id();
            $table->integer('book_journal_id');
            $table->date('date');
            $table->integer('inventory_id');
            $table->decimal('amount', 15, 2);
            $table->string('type_mutasi');
            $table->decimal('nilai_buku', 15, 2);
            $table->string('journal_number')->nullable();
            $table->integer('journal_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kartu_inventories');
    }
};
