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

        Schema::table('chart_account_aliases', function (Blueprint $table) {
            $table->boolean('is_child')->default(false);
            $table->integer('level')->default(0);
            $table->string('reference_model')->nullable();
            $table->string('account_type')->nullable();;
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
