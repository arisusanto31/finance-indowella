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
        Schema::create('task_import_details', function (Blueprint $table) {
            $table->id();
            $table->integer('book_journal_id')->index();
            $table->integer('task_import_id')->index();
            $table->string('type')->index();
            $table->json('payload')->nullable();
            $table->string('status')->default('queued');
            $table->string('journal_number')->nullable();
            $table->json('journal_id')->nullable();
            $table->string('error_message')->nullable();
            $table->datetime('processed_at')->nullable();
            $table->datetime('finished_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_import_details');
    }
};
