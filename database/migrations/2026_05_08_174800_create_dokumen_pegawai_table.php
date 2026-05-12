<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dokumen_pegawai', function (Blueprint $table) {
            $table->id('id_dokumen');
            $table->unsignedBigInteger('id_pegawai');
            $table->string('nama_dokumen', 150);
            $table->string('file_dokumen');
            $table->timestamps();

            $table->foreign('id_pegawai')
                ->references('id_pegawai')
                ->on('pegawai')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen_pegawai');
    }
};
