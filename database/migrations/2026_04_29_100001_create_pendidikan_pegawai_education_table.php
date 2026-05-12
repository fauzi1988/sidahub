<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /* Pulihkan migrasi yang gagal di tengah (tabel terbentuk tanpa FK) */
        if (Schema::hasTable('pendidikan_pegawai')) {
            Schema::drop('pendidikan_pegawai');
        }

        Schema::create('pendidikan_pegawai', function (Blueprint $table) {
            $table->id('id_pendidikan');
            $table->unsignedBigInteger('id_pegawai');
            $table->enum('tingkat', ['S1', 'S2', 'S3', 'D3']);
            $table->string('jurusan', 150);
            $table->string('nama_institusi', 200);
            $table->unsignedSmallInteger('tahun_lulus');
            $table->timestamps();

            /* Nama FK eksplisit: constraint lama masih bernama ...pendidikan_pegawai... setelah rename ke jabatan_pegawai */
            $table->foreign('id_pegawai', 'fk_pendidikan_education_id_pegawai')
                ->references('id_pegawai')
                ->on('pegawai')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendidikan_pegawai');
    }
};
