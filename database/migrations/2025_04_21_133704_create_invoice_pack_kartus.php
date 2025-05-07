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
        Schema::create('detail_kartu_invoices', function (Blueprint $table) {
            $table->id();
            $table->integer('book_journal_id');
            $table->integer('invoice_pack_id');
            $table->integer('sales_order_id')->nullable();
            $table->string('invoice_number');
            $table->string('kartu_type')->nullable();
            $table->integer('kartu_id')->nullable();
            $table->integer('journal_id')->nullable();
            $table->decimal('amount_journal',15,2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_pack_kartus');
    }
};
