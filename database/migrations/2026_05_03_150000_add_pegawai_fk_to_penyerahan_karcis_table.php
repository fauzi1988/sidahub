<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penyerahan_karcis', function (Blueprint $table) {
            $table->foreignId('pihak_pertama_id_pegawai')
                ->nullable()
                ->after('tanggal')
                ->constrained('pegawai', 'id_pegawai')
                ->nullOnDelete();
            $table->foreignId('pihak_kedua_id_pegawai')
                ->nullable()
                ->after('pihak_pertama_id_pegawai')
                ->constrained('pegawai', 'id_pegawai')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('penyerahan_karcis', function (Blueprint $table) {
            $table->dropForeign(['pihak_pertama_id_pegawai']);
            $table->dropForeign(['pihak_kedua_id_pegawai']);
            $table->dropColumn(['pihak_pertama_id_pegawai', 'pihak_kedua_id_pegawai']);
        });
    }
};
