<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('other_persons', function (Blueprint $table) {
            $table->softDeletes(); // nambahin kolom deleted_at
        });
    }

    public function down(): void
    {
        Schema::table('other_persons', function (Blueprint $table) {
            $table->dropSoftDeletes(); // rollback: hapus deleted_at
        });
    }
};
