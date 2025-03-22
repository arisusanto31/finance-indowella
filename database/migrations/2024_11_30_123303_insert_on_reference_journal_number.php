<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InsertOnReferenceJournalNumber extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('transactions',function($table){
            $table->string('journal_number')->nullable();
        });

        Schema::table('stock_errors',function($table){
            $table->string('journal_number')->nullable();
        });

        Schema::table('purchasings',function($table){
            $table->string('journal_number')->nullable();
        });

        //task_payments
        Schema::table('karyawan_task_payment_harians',function($table){
            $table->string('journal_number')->nullable();
        });

        //inventory
        Schema::table('inventories',function($table){
            $table->string('journal_number')->nullable();
        });
        //event inventory
        Schema::table('event_inventories',function($table){
            $table->string('journal_number')->nullable();
        });
        //prepaid
        Schema::table('prepaid_expenses',function($table){
            $table->string('journal_number')->nullable();
        });
        //event prepaid
        Schema::table('event_prepaid_expenses',function($table){
            $table->string('journal_number')->nullable();
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
