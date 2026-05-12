<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan_mingguan', function (Blueprint $table) {
            if (! Schema::hasColumn('laporan_mingguan', 'tahun')) {
                $table->unsignedSmallInteger('tahun')->nullable()->after('tanggal');
            }
        });

        DB::table('laporan_mingguan')
            ->whereNull('tahun')
            ->get(['id', 'tanggal', 'created_at'])
            ->each(function ($row): void {
                $fallbackDate = $row->tanggal ?? $row->created_at;
                $tahun = (int) date('Y', strtotime((string) $fallbackDate));
                DB::table('laporan_mingguan')
                    ->where('id', $row->id)
                    ->update(['tahun' => $tahun > 0 ? $tahun : (int) date('Y')]);
            });

        DB::statement('ALTER TABLE laporan_mingguan MODIFY tahun SMALLINT UNSIGNED NOT NULL');
    }

    public function down(): void
    {
        Schema::table('laporan_mingguan', function (Blueprint $table) {
            if (Schema::hasColumn('laporan_mingguan', 'tahun')) {
                $table->dropColumn('tahun');
            }
        });
    }
};
