<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('karyawans', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('npwp');
            $table->string('nik');
            $table->string('jabatan');
            $table->date('date_masuk');
            $table->date('date_keluar');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('karyawans');
    }
};

