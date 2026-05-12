<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operasional', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penyerahan_karcis_id')->unique()->constrained('penyerahan_karcis')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('nomor_bast', 120);
            $table->string('nama_penanggungjawab', 150);
            $table->string('bukti_setor');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operasional');
    }
};
