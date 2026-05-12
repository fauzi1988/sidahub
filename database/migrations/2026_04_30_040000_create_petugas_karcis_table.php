<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('petugas_karcis', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_induk_pegawai', 50)->unique();
            $table->string('nama_pegawai', 150);
            $table->text('alamat');
            $table->string('nomor_telepon', 20);
            $table->enum('status', ['PNS', 'PPPK', 'Tenaga Kontrak']);
            $table->string('tempat_tugas', 150);
            $table->string('foto')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('petugas_karcis');
    }
};
