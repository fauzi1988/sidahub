<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_keluar', function (Blueprint $table) {
            $table->id('id_surat_keluar');
            $table->string('nomor_surat', 120)->nullable()->unique();
            $table->date('tanggal_surat');
            $table->date('tanggal_kirim')->nullable();
            $table->string('perihal', 255);
            $table->string('tujuan_surat', 255);
            $table->text('alamat_tujuan')->nullable();
            $table->enum('jenis_surat', ['surat_dinas', 'nota_dinas', 'surat_tugas', 'undangan', 'memo_internal', 'balasan']);
            $table->enum('sifat_surat', ['biasa', 'penting', 'segera', 'rahasia'])->default('biasa');
            $table->enum('prioritas', ['normal', 'tinggi', 'sangat_tinggi'])->default('normal');
            $table->enum('status', [
                'draft',
                'menunggu_verifikasi',
                'revisi_admin',
                'menunggu_review_substansi',
                'revisi_substansi',
                'menunggu_paraf',
                'menunggu_ttd',
                'disetujui',
                'dikirim',
                'diarsipkan',
                'dibatalkan',
            ])->default('draft');
            $table->unsignedBigInteger('id_pegawai_pengusul')->nullable();
            $table->unsignedBigInteger('id_pegawai_penandatangan')->nullable();
            $table->text('ringkasan')->nullable();
            $table->longText('isi_surat')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->foreign('id_pegawai_pengusul', 'fk_surat_keluar_pengusul')
                ->references('id_pegawai')
                ->on('pegawai')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreign('id_pegawai_penandatangan', 'fk_surat_keluar_penandatangan')
                ->references('id_pegawai')
                ->on('pegawai')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->index('tanggal_surat');
            $table->index('status');
            $table->index('jenis_surat');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_keluar');
    }
};
