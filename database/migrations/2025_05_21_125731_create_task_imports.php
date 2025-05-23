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
        Schema::create('task_imports', function (Blueprint $table) {
            $table->id();
            $table->integer('book_journal_id');
            $table->string('type')->index();
            $table->string('description');
            $table->string('status')->default('queued');

            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_imports');
    }
};
