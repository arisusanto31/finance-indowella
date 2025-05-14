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
        Schema::create('invoice_packs', function (Blueprint $table) {
            $table->id();
            $table->integer('book_journal_id');
            $table->string('invoice_number')->unique();
            $table->string('person_type');
            $table->integer('person_id');
            $table->string('reference_model');
            $table->date('invoice_date')->nullable();
            $table->decimal('total_price', 20, 2)->nullable();
            $table->string('status')->default('draft');
            $table->integer('toko_id');
            $table->integer('sales_order_id')->nullable();
            $table->integer('purchase_order_id')->nullable();
            $table->integer('reference_id')->nullable();
            $table->string('reference_type')->nullable();

            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('invoice_packs');
    }
};
