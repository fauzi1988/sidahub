<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('surat_masuk') && ! Schema::hasTable('arsip_surat_keluar')) {
            Schema::rename('surat_masuk', 'arsip_surat_keluar');
        }

        Schema::create('surat_masuk', function (Blueprint $table) {
            $table->id('id_surat_masuk');
            $table->string('nomor_agenda', 80)->nullable()->unique();
            $table->string('nomor_surat_pengirim', 120)->nullable();
            $table->date('tanggal_surat');
            $table->date('tanggal_terima');
            $table->string('perihal', 255);
            $table->string('pengirim', 255);
            $table->string('sifat_surat', 30)->default('biasa');
            $table->text('ringkasan')->nullable();
            $table->json('lampiran')->nullable();
            $table->string('status', 40)->default('tercatat');
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('surat_keluar_balasan_id')->nullable();
            $table->timestamp('agenda_at')->nullable();
            $table->timestamp('forwarded_to_kadis_at')->nullable();
            $table->timestamp('disposed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('alasan_batal')->nullable();
            $table->timestamps();

            $table->foreign('surat_keluar_balasan_id', 'fk_surat_masuk_balasan_keluar')
                ->references('id_surat_keluar')
                ->on('surat_keluar')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->index('status');
            $table->index('tanggal_terima');
            $table->index('pengirim');
        });

        Schema::create('surat_masuk_disposisi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('surat_masuk_id');
            $table->foreignId('from_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('to_pegawai_id')->nullable();
            $table->string('to_unit_kerja', 150)->nullable();
            $table->string('tingkat', 20)->default('unit');
            $table->text('instruksi');
            $table->date('batas_waktu')->nullable();
            $table->string('status', 20)->default('aktif');
            $table->text('catatan_tindak_lanjut')->nullable();
            $table->timestamp('selesai_at')->nullable();
            $table->timestamps();

            $table->foreign('surat_masuk_id', 'fk_disposisi_surat_masuk')
                ->references('id_surat_masuk')
                ->on('surat_masuk')
                ->cascadeOnDelete();
            $table->foreign('to_pegawai_id', 'fk_disposisi_pegawai')
                ->references('id_pegawai')
                ->on('pegawai')
                ->nullOnDelete();
        });

        Schema::create('surat_masuk_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('surat_masuk_id');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 60);
            $table->string('from_status', 40)->nullable();
            $table->string('to_status', 40)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('surat_masuk_id', 'fk_log_surat_masuk')
                ->references('id_surat_masuk')
                ->on('surat_masuk')
                ->cascadeOnDelete();
        });

        $legacy = DB::table('user_permissions')
            ->where('permission_key', 'kepegawaian.persuratan.surat_masuk')
            ->pluck('user_id')
            ->unique();

        foreach ($legacy as $userId) {
            $exists = DB::table('user_permissions')
                ->where('user_id', $userId)
                ->where('permission_key', 'kepegawaian.persuratan.arsip_surat_keluar')
                ->exists();

            if (! $exists) {
                DB::table('user_permissions')->insert([
                    'user_id' => $userId,
                    'permission_key' => 'kepegawaian.persuratan.arsip_surat_keluar',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_masuk_logs');
        Schema::dropIfExists('surat_masuk_disposisi');
        Schema::dropIfExists('surat_masuk');

        if (Schema::hasTable('arsip_surat_keluar')) {
            Schema::rename('arsip_surat_keluar', 'surat_masuk');
        }
    }
};
