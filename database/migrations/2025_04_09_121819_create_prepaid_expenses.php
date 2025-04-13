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
        Schema::create('prepaid_expenses', function (Blueprint $table) {
            $table->id();
            $table->integer('book_journal_id');
            $table->string('name')->unique();
            $table->string('type_bdd');
            $table->date('date');
            $table->decimal('nilai_perolehan', 15, 2);
            $table->integer('periode');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prepaid_expenses');
    }
};
