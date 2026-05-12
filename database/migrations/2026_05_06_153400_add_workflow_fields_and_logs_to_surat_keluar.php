<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_keluar', function (Blueprint $table) {
            $table->timestamp('submitted_at')->nullable()->after('catatan');
            $table->timestamp('reviewed_at')->nullable()->after('submitted_at');
            $table->timestamp('verified_at')->nullable()->after('reviewed_at');
            $table->timestamp('forwarded_to_kadis_at')->nullable()->after('verified_at');
            $table->timestamp('signed_at')->nullable()->after('forwarded_to_kadis_at');

            $table->unsignedBigInteger('reviewed_by_kabid_user_id')->nullable()->after('signed_at');
            $table->unsignedBigInteger('verified_by_sekretariat_user_id')->nullable()->after('reviewed_by_kabid_user_id');
            $table->unsignedBigInteger('signed_by_kadis_user_id')->nullable()->after('verified_by_sekretariat_user_id');

            $table->foreign('reviewed_by_kabid_user_id', 'fk_surat_keluar_reviewed_by')
                ->references('id')->on('users')
                ->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('verified_by_sekretariat_user_id', 'fk_surat_keluar_verified_by')
                ->references('id')->on('users')
                ->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('signed_by_kadis_user_id', 'fk_surat_keluar_signed_by')
                ->references('id')->on('users')
                ->nullOnDelete()->cascadeOnUpdate();
        });

        Schema::create('surat_keluar_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('surat_keluar_id');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $table->string('action', 60);
            $table->string('from_status', 40)->nullable();
            $table->string('to_status', 40)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('surat_keluar_id', 'fk_surat_keluar_logs_surat')
                ->references('id_surat_keluar')
                ->on('surat_keluar')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->index(['surat_keluar_id', 'created_at']);
            $table->index('action');
            $table->index('to_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_keluar_logs');

        Schema::table('surat_keluar', function (Blueprint $table) {
            $table->dropForeign('fk_surat_keluar_reviewed_by');
            $table->dropForeign('fk_surat_keluar_verified_by');
            $table->dropForeign('fk_surat_keluar_signed_by');

            $table->dropColumn([
                'submitted_at',
                'reviewed_at',
                'verified_at',
                'forwarded_to_kadis_at',
                'signed_at',
                'reviewed_by_kabid_user_id',
                'verified_by_sekretariat_user_id',
                'signed_by_kadis_user_id',
            ]);
        });
    }
};
