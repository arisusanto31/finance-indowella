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
        Schema::create('link_reference_cash_kinds', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('book_journal_id');
            $table->string('cash_kind_name');
            $table->integer('code_group');
            $table->unique(['code_group','cash_kind_name','book_journal_id'],'unique_cash_kind_code_group');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('link_reference_cash_kinds');
    }
};
