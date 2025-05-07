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
        Schema::create('kartu_dp_sales', function (Blueprint $table) {
            $table->id();
            $table->integer('book_journal_id');
            $table->string('type');
            $table->decimal('code_group', 6, 0);
            $table->decimal('lawan_code_group', 6, 0);
            $table->string('code_group_name', 6, 0);
            $table->string('package_number')->nullable();
            $table->date('invoice_date')->nullable();
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
            $table->integer('journal_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kartu_dp_sales');
    }
};
