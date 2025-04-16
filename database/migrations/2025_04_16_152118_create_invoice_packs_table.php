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
            $table->string('invoice_number')->unique();
            $table->unsignedBigInteger('customer_id');
            $table->date('invoice_date')->nullable();
            $table->decimal('total_price', 20, 2)->nullable();
            $table->string('status')->default('pending');
            $table->string('bukti_file')->nullable();
         
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        
            $table->timestamps(); 
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('invoice_packs');
    }
};
