<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_keluar', function (Blueprint $table) {
            $table->string('unit_kerja', 150)->nullable()->after('id_pegawai_penandatangan');
            $table->foreignId('created_by_user_id')->nullable()->after('unit_kerja')
                ->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->json('lampiran')->nullable()->after('catatan');
            $table->string('verification_code', 16)->nullable()->unique()->after('lampiran');
            $table->timestamp('cancelled_at')->nullable()->after('signed_at');
            $table->timestamp('archived_at')->nullable()->after('cancelled_at');
            $table->text('alasan_batal')->nullable()->after('archived_at');

            $table->index('unit_kerja');
            $table->index('created_by_user_id');
        });

        Schema::table('surat_keluar', function (Blueprint $table) {
            $table->foreign('ttd_management_id', 'fk_surat_keluar_ttd')
                ->references('id_ttd')
                ->on('manajemen_ttd')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('surat_keluar', function (Blueprint $table) {
            $table->dropForeign('fk_surat_keluar_ttd');
            $table->dropIndex(['unit_kerja']);
            $table->dropIndex(['created_by_user_id']);
            $table->dropIndex(['surat_masuk_id']);
            $table->dropColumn([
                'unit_kerja',
                'created_by_user_id',
                'lampiran',
                'verification_code',
                'cancelled_at',
                'archived_at',
                'alasan_batal',
            ]);
        });
    }
};
