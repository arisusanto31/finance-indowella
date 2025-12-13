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
        schema::table('detail_kartu_invoices', function ($table) {
            $table->string('journal_number')->nullable()->after('journal_id');
            $table->string('account_name')->nullable()->after('journal_number');
            $table->decimal('account_code_group', 6, 0)->nullable()->after('account_name');
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
