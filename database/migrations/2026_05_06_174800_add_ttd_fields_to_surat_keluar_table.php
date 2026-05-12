<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_keluar', function (Blueprint $table) {
            $table->enum('jenis_ttd', ['elektronik', 'basah', 'scan'])->nullable()->after('signed_by_kadis_user_id');
            $table->unsignedBigInteger('ttd_management_id')->nullable()->after('jenis_ttd');
            $table->index('jenis_ttd');
            $table->index('ttd_management_id');
        });
    }

    public function down(): void
    {
        Schema::table('surat_keluar', function (Blueprint $table) {
            $table->dropIndex(['jenis_ttd']);
            $table->dropIndex(['ttd_management_id']);
            $table->dropColumn(['jenis_ttd', 'ttd_management_id']);
        });
    }
};

