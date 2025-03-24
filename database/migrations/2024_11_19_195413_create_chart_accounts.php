<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChartAccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chart_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code_group')->unique();
            $table->string('account_type');
            $table->integer('parent_id')->nullable();
            $table->integer('is_deleted')->nullable();
            $table->boolean('is_child')->nullable();
            $table->integer('level')->nullable();
            $table->datetime('deleted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chart_accounts');
    }
}
