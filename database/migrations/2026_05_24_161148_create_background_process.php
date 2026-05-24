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
        Schema::create('background_process', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('monitoring_url');
            $table->decimal('progress', 5, 2)->default(0);
            $table->integer('total_task')->default(0);
            $table->integer('success_task')->default(0);
            $table->integer('failed_task')->default(0);
            $table->string('status')->default('pending');
            $table->string('description_process')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('background_process');
    }
};
