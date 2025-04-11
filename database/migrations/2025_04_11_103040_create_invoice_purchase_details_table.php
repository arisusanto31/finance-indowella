<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_purchase_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->string('invoice_number')->nullable();
            $table->unsignedBigInteger('stock_id');
            $table->text('description')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->string('unit')->nullable();
            $table->decimal('quantity', 15, 2)->default(0);
            $table->string('unit_backend')->nullable();
            $table->decimal('qty_backend', 15, 2)->default(0);
            $table->decimal('total_price', 20, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->string('journal_number')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->integer('reference_id')->nullable();
            $table->string('reference_type')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_purchase_details');
    }
};

