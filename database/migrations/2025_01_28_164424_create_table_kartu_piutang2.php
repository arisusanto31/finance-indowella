<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableKartuPiutang2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('kartu_piutangs', function (Blueprint $table) {
            $table->id();
            $table->integer('book_journal_id');
            $table->string('type');
            $table->integer('transaction_id')->nullable();
            $table->integer('code_group_piutang')->nullable();
            $table->string('package_number')->nullable();
            $table->string('description')->nullable();
            $table->decimal('amount_kredit', 12, 2);
            $table->decimal('amount_debet', 12, 2);
            $table->decimal('amount_saldo_transaction', 14, 2)->nullable();
            $table->decimal('amount_saldo_factur', 14, 2)->nullable();
            $table->decimal('amount_saldo_person', 14, 2)->nullable();
            $table->integer('reference_id')->nullable();
            $table->string('reference_type')->nullable();
            $table->integer('person_id')->nullable();
            $table->string('person_type')->nullable();
            $table->string('journal_number')->nullable();
            $table->string('code_group', 10)->nullable();
            $table->string('lawan_code_group', 10)->nullable();
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
        Schema::dropIfExists('table_kartu_piutang2');
    }
}
