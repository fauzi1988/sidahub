<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_masuk', function (Blueprint $table) {
            $table->id('id_surat_masuk');
            $table->string('nomor_surat', 120)->nullable();
            $table->date('tanggal_surat');
            $table->date('tanggal_terima')->nullable();
            $table->string('perihal', 255);
            $table->string('pengirim', 255);
            $table->string('sifat_surat', 30)->default('biasa');
            $table->unsignedBigInteger('surat_keluar_id')->nullable();
            $table->text('ringkasan')->nullable();
            $table->json('lampiran')->nullable();
            $table->timestamps();

            $table->foreign('surat_keluar_id', 'fk_surat_masuk_keluar')
                ->references('id_surat_keluar')
                ->on('surat_keluar')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->index('tanggal_terima');
            $table->index('nomor_surat');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('surat_masuk');
    }
};
