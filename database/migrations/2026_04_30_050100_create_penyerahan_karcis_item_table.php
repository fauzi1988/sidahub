<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penyerahan_karcis_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penyerahan_karcis_id')->constrained('penyerahan_karcis')->cascadeOnDelete();
            $table->string('uraian', 255);
            $table->unsignedInteger('banyak_buku');
            $table->decimal('tarif', 15, 2)->nullable();
            $table->string('nomor_seri_awal', 30);
            $table->string('nomor_seri_akhir', 30);
            $table->string('keterangan', 150)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penyerahan_karcis_item');
    }
};
