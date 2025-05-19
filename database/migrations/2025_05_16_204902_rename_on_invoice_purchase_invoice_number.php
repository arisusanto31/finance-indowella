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
        // Rename the column 'invoice_number' to 'invoice_purchase_number' in the 'invoice_purchase_details' table
        Schema::table('invoice_purchase_details', function (Blueprint $table) {
            $table->renameColumn('invoice_number', 'invoice_pack_number');
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
