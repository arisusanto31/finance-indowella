<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InsertOnJournalsAmountSaldo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('journals',function($table){
            $table->decimal('index_date',14,0);
            $table->decimal('amount_saldo',15,2)->after('amount_kredit');
            $table->unique(['index_date', 'chart_account_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
