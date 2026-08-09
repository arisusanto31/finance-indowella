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
        Schema::table('invoice_packs',function($table){
            $table->index('invoice_number','idx_invoice_number');
            $table->index('is_final','idx_is_final');
            $table->index('is_mark','idx_is_mark');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('invoice_packs',function($table){
            $table->dropIndex('idx_invoice_number');
        });
    }
};
