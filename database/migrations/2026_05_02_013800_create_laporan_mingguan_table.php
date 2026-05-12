<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_mingguan', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('nama_petugas', 150);
            $table->string('tempat_tugas', 150)->nullable();
            $table->string('nama_karcis', 255);
            $table->decimal('harga_satuan', 15, 2)->default(0);
            $table->unsignedInteger('jumlah_karcis')->default(0);
            $table->unsignedInteger('lembar_terjual')->default(0);
            $table->decimal('total_penjualan', 18, 2)->default(0);
            $table->decimal('setor_kada', 18, 2)->default(0);
            $table->date('tanggal_setor')->nullable();
            $table->string('ket', 200)->nullable();
            $table->unsignedTinyInteger('minggu_ke')->nullable();
            $table->string('bukti_setor')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_mingguan');
    }
};
