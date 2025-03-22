<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableKartuUtang2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('kartu_utangs');
        Schema::create('kartu_utangs', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->integer('purchasing_id')->nullable();
            $table->string('factur_supplier_number')->nullable();
            $table->string('description')->nullable();
            $table->decimal('amount_kredit',12,2);
            $table->decimal('amount_debet',12,2);
            $table->decimal('amount_saldo_purchase',14,2)->nullable();
            $table->decimal('amount_saldo_factur',14,2)->nullable();
            $table->decimal('amount_saldo_person',14,2)->nullable();
            $table->integer('reference_id')->nullable();
            $table->string('reference_type')->nullable();
            $table->integer('person_id')->nullable();
            $table->string('person_type')->nullable();
            $table->string('journal_number')->nullable();
            
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
        Schema::dropIfExists('kartu_utangs');
    }
}
