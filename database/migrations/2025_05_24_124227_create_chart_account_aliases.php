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
        Schema::create('chart_account_aliases', function (Blueprint $table) {
            $table->id();
            $table->integer('book_journal_id')->index();
            $table->integer('chart_account_id')->index();
            $table->integer('code_group')->index();
            $table->string('name')->nullable();
            $table->unique(['book_journal_id', 'chart_account_id', 'code_group'], 'book_journal_chart_account_code_group_unique');  
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chart_account_aliases');
    }
};
