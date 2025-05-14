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
            $table->decimal('code_group', 6, 0);
            $table->string('code_group_name');

            $table->string('sales_order_number')->nullable();;
            $table->integer('sales_order_id')->nullable();
            $table->string('invoice_pack_number')->nullable();
            $table->integer('invoice_pack_id')->nullable();
            $table->string('purchase_order_number')->nullable();
            $table->integer('purchase_order_id')->nullable();

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
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kartu_stocks');
    }
}
