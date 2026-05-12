<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penyerahan_karcis', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_bast', 120);
            $table->date('tanggal');
            $table->string('pihak_pertama_nama', 150);
            $table->string('pihak_pertama_nip', 30)->nullable();
            $table->string('pihak_pertama_jabatan', 150)->nullable();
            $table->string('pihak_pertama_instansi', 200)->nullable();
            $table->text('pihak_pertama_alamat')->nullable();
            $table->string('pihak_kedua_nama', 150);
            $table->string('pihak_kedua_nip', 30)->nullable();
            $table->string('pihak_kedua_jabatan', 150)->nullable();
            $table->string('pihak_kedua_instansi', 200)->nullable();
            $table->text('pihak_kedua_alamat')->nullable();
            $table->string('mengetahui_nama', 150)->nullable();
            $table->string('mengetahui_nip', 30)->nullable();
            $table->string('file_surat')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penyerahan_karcis');
    }
};
