<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pendidikan_pegawai') && ! Schema::hasTable('jabatan_pegawai')) {
            Schema::rename('pendidikan_pegawai', 'jabatan_pegawai');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('jabatan_pegawai') && ! Schema::hasTable('pendidikan_pegawai')) {
            Schema::rename('jabatan_pegawai', 'pendidikan_pegawai');
        }
    }
};
