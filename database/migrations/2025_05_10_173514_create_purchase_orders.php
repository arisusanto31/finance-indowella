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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->integer('book_journal_id');
            $table->integer('toko_id');
            $table->integer('supplier_id');
            $table->string('purchase_order_number')->unique();
            $table->string('status_payment')->default('unpaid');
            $table->string('status_delivery')->default('pending');
            $table->string('status')->default('draft');
            $table->integer('total_price')->default(0);
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
        Schema::dropIfExists('purchase_orders');
    }
};
