<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJournals extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('journals', function (Blueprint $table) {
            $table->id();
            $table->integer('book_journal_id');
            $table->integer('chart_account_id');
            $table->decimal('index_date', 14, 0);
            $table->string('journal_number');
            $table->string('code_group');
            $table->string('description');
            $table->decimal('amount_debet', 15, 2)->nullable();
            $table->decimal('amount_kredit', 15, 2)->nullale();
            $table->decimal('amount_saldo', 15, 2);
            $table->integer('reference_id')->nullable();
            $table->string('reference_type')->nullable();
            $table->string('reference_model')->nullable();
            $table->integer('verified_by')->nullable();
            $table->integer('is_auto_generated')->nullable();
            $table->string('lawan_code_group', 10)->nullable();
            $table->integer('is_backdate')->nullable();
            $table->integer('user_backdate_id')->nullable();
            $table->integer('toko_id')->nullable();
            $table->unique(['index_date', 'chart_account_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('journals');
    }
}
