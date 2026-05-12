<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendidikan_pegawai', function (Blueprint $table) {
            $table->id('id_jabatan');
            $table->unsignedBigInteger('id_pegawai');
            $table->string('jabatan', 150);
            $table->string('unit_kerja', 150);
            $table->string('pangkat_golongan', 100)->nullable();
            $table->date('tmt');
            $table->enum('status_jabatan', ['Aktif', 'Tidak Aktif'])->default('Aktif');
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
        Schema::dropIfExists('pendidikan_pegawai');
    }
};
