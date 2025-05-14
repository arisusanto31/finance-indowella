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
        Schema::create('purchase_details', function (Blueprint $table) {
            $table->id();
            $table->integer('book_journal_id');
            $table->string('purchase_order_number');
            $table->integer('toko_id');
            $table->integer('purchase_order_id')->nullable();
            $table->integer('stock_id');
            $table->text('description')->nullable();
            $table->decimal('price', 15, 2);
            $table->string('unit');
            $table->decimal('quantity', 15, 2);
            $table->decimal('total_price', 20, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->unsignedBigInteger('customer_id');
            $table->integer('reference_id')->nullable();
            $table->string('reference_type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_details');
    }
};
