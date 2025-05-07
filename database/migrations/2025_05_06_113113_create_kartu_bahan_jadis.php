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
        Schema::create('kartu_bahan_jadis', function (Blueprint $table) {
            $table->id();
            $table->integer('book_journal_id');
            $table->decimal('code_group', 6, 0);
            $table->string('code_group_name');
            $table->string('spk_number');
            $table->string('sale_order_id')->nullable();

            $table->integer('stock_id');
            $table->decimal('mutasi_qty_backend', 10, 2);
            $table->string('unit_backend');
            $table->decimal('mutasi_quantity', 10, 2);
            $table->string('unit');
            $table->decimal('mutasi_rupiah_on_unit', 10, 2);
            $table->decimal('mutasi_rupiah_total', 14, 2);
            $table->decimal('saldo_qty_backend', 10, 2);
            $table->decimal('saldo_rupiah_total', 14, 2);
            $table->integer('is_uploaded')->nullable();
            $table->integer('reference_id')->nullable(); //ini nanti bisa kita link ke invoice
            $table->string('reference_type')->nullable();
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
        Schema::dropIfExists('kartu_bahan_jadis');
    }
};
