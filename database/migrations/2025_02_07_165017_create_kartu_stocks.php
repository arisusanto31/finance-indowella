<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKartuStocks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kartu_stocks', function (Blueprint $table) {
            $table->id();
            $table->integer('book_journal_id');
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
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kartu_stocks');
    }
}
