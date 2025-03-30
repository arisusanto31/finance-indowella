<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('ktp')->nullable();
            $table->string('npwp')->nullable();
            $table->string('purchase_info')->nullable();
            $table->boolean('is_deleted')->nullable();
            $table->softDeletes();
            $table->timestamps();

            
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
