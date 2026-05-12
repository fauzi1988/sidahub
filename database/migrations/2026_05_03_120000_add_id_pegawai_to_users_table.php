<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('id_pegawai')
                ->nullable()
                ->after('is_super_admin')
                ->constrained('pegawai', 'id_pegawai')
                ->nullOnDelete();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unique('id_pegawai');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['id_pegawai']);
            $table->dropForeign(['id_pegawai']);
            $table->dropColumn('id_pegawai');
        });
    }
};
