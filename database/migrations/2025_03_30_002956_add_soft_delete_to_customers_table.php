<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->tinyInteger('is_deleted')->nullable()->after('id');
            $table->timestamp('deleted_at')->nullable()->after('is_deleted');
        });
    }

    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['is_deleted', 'deleted_at']);
        });
    }
};
