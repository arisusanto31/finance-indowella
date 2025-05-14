<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_sale_details', function (Blueprint $table) {
            $table->id();
            $table->integer('book_journal_id');
            $table->integer('toko_id');

            $table->unsignedBigInteger('invoice_pack_id');
            $table->string('invoice_pack_number');
            $table->integer('sales_order_id')->nullable();
            $table->string('sales_order_number')->nullable();

            $table->integer('stock_id');
            $table->string('custom_stock_name')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 15, 2);
            $table->string('unit');
            $table->decimal('quantity', 15, 2);
            $table->string('unit_backend');
            $table->decimal('qty_backend', 15, 2);
            $table->decimal('total_price', 20, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->string('journal_number')->nullable();
            $table->integer('journal_id'); // ✅ Benerin bagian ini
            $table->unsignedBigInteger('customer_id');
            $table->integer('reference_id')->nullable();
            $table->string('reference_type')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_sale_details');
    }
};
