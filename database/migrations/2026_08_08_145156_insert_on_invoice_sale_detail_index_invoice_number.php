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

        Schema::table('invoice_sale_details', function ($table) {
            $table->index('invoice_pack_number', 'idx_invoice_pack_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('invoice_sale_details', function ($table) {
            $table->dropIndex('idx_invoice_pack_number');
        });
    }
};
