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
        Schema::table('kartu_piutangs',function($table){
            $table->dropUnique('kartu_piutangs_index_date_unique');
            $table->unique(['book_journal_id','index_date'],'kartu_piutangs_index_date_unique');
            $table->index(['book_journal_id','index_date','invoice_pack_number'],'kartu_piutangs_index_date_invoice_pack_number_index');
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
