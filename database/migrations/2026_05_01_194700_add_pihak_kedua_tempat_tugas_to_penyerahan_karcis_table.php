<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penyerahan_karcis', function (Blueprint $table) {
            if (! Schema::hasColumn('penyerahan_karcis', 'pihak_kedua_tempat_tugas')) {
                $table->string('pihak_kedua_tempat_tugas', 150)->nullable()->after('pihak_kedua_jabatan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('penyerahan_karcis', function (Blueprint $table) {
            if (Schema::hasColumn('penyerahan_karcis', 'pihak_kedua_tempat_tugas')) {
                $table->dropColumn('pihak_kedua_tempat_tugas');
            }
        });
    }
};
