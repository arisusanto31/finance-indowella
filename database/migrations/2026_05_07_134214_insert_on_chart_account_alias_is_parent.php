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
            $table->integer('parent_id')->nullable()->after('is_child');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chart_account_aliases', function (Blueprint $table) {
            $table->dropColumn('parent_id');
        });
    }
};
