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
            $table->integer('chart_account_id');
            $table->string('journal_number');
            $table->string('code_group');
            $table->string('description');
            $table->decimal('amount_debet',15,2)->nullable();
            $table->decimal('amount_kredit',15,2)->nullale();
            $table->integer('reference_id')->nullable();
            $table->string('reference_type')->nullable();
            $table->integer('verified_by')->nullable();
            $table->integer('is_auto_generated')->nullable();
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
