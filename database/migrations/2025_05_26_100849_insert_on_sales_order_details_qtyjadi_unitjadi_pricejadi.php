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
        Schema::table('sales_order_details', function (Blueprint $table) {
            $table->decimal('qtyjadi', 20, 4)->default(0)->after('quantity');
            $table->string('unitjadi')->default("??")->after('unit');
            $table->decimal('pricejadi', 20, 4)->default(0)->after('price');
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
