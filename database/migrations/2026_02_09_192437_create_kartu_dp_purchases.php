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
        Schema::create('kartu_dp_purchases', function (Blueprint $table) {
            $table->id();
            $table->string('tag')->nullable()->after('id');
            $table->decimal('index_date', 15, 2)->unique();
            $table->decimal('index_date_group', 12, 0);
            $table->integer('book_journal_id')->nullable();
            $table->string('type');
            $table->decimal('code_group', 6, 0);
            $table->decimal('lawan_code_group', 6, 0);
            $table->string('code_group_name');
            $table->integer('invoice_pack_id')->nullable();
            $table->string('invoice_pack_number')->nullable();
            $table->date('date');
            $table->string('description');
            $table->decimal('amount_kredit', 12, 2);
            $table->decimal('amount_debet', 12, 2);
            $table->decimal('amount_saldo_transaction', 14, 2);
            $table->decimal('amount_saldo_factur', 14, 2);
            $table->decimal('amount_saldo_person', 14, 2);
            $table->integer('reference_id')->nullable();
            $table->string('reference_type')->nullable();
            $table->integer('person_id')->nullable();
            $table->string('person_type')->nullable();
            $table->string('journal_number')->nullable();
            $table->integer('journal_id')->nullable();
            $table->index(['book_journal_id', 'index_date_group', 'index_date']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kartu_dp_purchases');
    }
};
