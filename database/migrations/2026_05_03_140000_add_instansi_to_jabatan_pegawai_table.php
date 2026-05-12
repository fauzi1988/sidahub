<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jabatan_pegawai', function (Blueprint $table) {
            $table->string('instansi', 200)
                ->default('Dinas Perhubungan')
                ->after('id_pegawai');
        });
    }

    public function down(): void
    {
        Schema::table('jabatan_pegawai', function (Blueprint $table) {
            $table->dropColumn('instansi');
        });
    }
};
