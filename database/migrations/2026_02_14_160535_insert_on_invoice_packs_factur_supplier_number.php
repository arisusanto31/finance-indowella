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
        Schema::table('invoice_packs', function (Blueprint $table) {
            $table->string('factur_supplier_number')->nullable()->after('fp_number');
        });
        Schema::table('invoice_purchase_details', function (Blueprint $table) {
            $table->string('fp_number')->nullable()->after('invoice_pack_number');
            $table->string('factur_supplier_number')->nullable()->after('fp_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('invoice_packs', function (Blueprint $table) {
            $table->dropColumn('factur_supplier_number');
        });
        Schema::table('invoice_purchase_details', function (Blueprint $table) {
            $table->dropColumn('fp_number');
            $table->dropColumn('factur_supplier_number');
        });
    }
};
