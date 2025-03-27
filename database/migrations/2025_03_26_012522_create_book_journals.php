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
        Schema::create('book_journals', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('type')->nullable();
            $table->string('theme')->nullable();
            $table->boolean('is_deleted')->nullable();
            $table->datetime('deleted_at')->nullable();
            $table->unique(['name', 'type']);
            $table->unique(['name', 'theme']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_journals');
    }
};
