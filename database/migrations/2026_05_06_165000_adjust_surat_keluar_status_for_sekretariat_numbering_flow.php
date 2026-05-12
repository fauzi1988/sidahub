<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE surat_keluar MODIFY status ENUM(
            'draft',
            'menunggu_verifikasi',
            'revisi_admin',
            'menunggu_review_substansi',
            'revisi_substansi',
            'menunggu_paraf',
            'menunggu_ttd',
            'ditandatangani',
            'disetujui',
            'dikirim',
            'diarsipkan',
            'dibatalkan'
        ) NOT NULL DEFAULT 'draft'");

        DB::statement("UPDATE surat_keluar SET status = 'disetujui' WHERE status = 'ditandatangani'");
        DB::statement("ALTER TABLE surat_keluar MODIFY status ENUM(
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
            'dibatalkan'
        ) NOT NULL DEFAULT 'draft'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE surat_keluar MODIFY status ENUM(
            'draft',
            'menunggu_verifikasi',
            'revisi_admin',
            'menunggu_review_substansi',
            'revisi_substansi',
            'menunggu_paraf',
            'menunggu_ttd',
            'ditandatangani',
            'disetujui',
            'dikirim',
            'diarsipkan',
            'dibatalkan'
        ) NOT NULL DEFAULT 'draft'");

        DB::statement("UPDATE surat_keluar SET status = 'ditandatangani' WHERE status = 'disetujui'");
        DB::statement("ALTER TABLE surat_keluar MODIFY status ENUM(
            'draft',
            'menunggu_verifikasi',
            'revisi_admin',
            'menunggu_review_substansi',
            'revisi_substansi',
            'menunggu_paraf',
            'menunggu_ttd',
            'ditandatangani',
            'dikirim',
            'diarsipkan',
            'dibatalkan'
        ) NOT NULL DEFAULT 'draft'");
    }
};

