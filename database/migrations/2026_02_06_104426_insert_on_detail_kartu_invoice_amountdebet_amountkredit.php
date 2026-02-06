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
        //
        Schema::table('detail_kartu_invoices', function (Blueprint $table) {
            $table->decimal('amount_debet', 16, 2)->default(0);
            $table->decimal('amount_kredit', 16, 2)->default(0)->after('amount_debet');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
