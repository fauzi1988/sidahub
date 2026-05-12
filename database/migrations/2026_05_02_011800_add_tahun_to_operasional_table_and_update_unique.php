<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operasional', function (Blueprint $table) {
            if (! Schema::hasColumn('operasional', 'tahun')) {
                $table->unsignedSmallInteger('tahun')->nullable()->after('tanggal');
            }
        });

        DB::table('operasional')
            ->whereNull('tahun')
            ->get(['id', 'tanggal', 'created_at'])
            ->each(function ($row): void {
                $fallbackDate = $row->tanggal ?? $row->created_at;
                $tahun = (int) date('Y', strtotime((string) $fallbackDate));
                DB::table('operasional')
                    ->where('id', $row->id)
                    ->update(['tahun' => $tahun > 0 ? $tahun : (int) date('Y')]);
            });

        DB::statement('ALTER TABLE operasional MODIFY tahun SMALLINT UNSIGNED NOT NULL');

        try {
            Schema::table('operasional', function (Blueprint $table) {
                $table->dropUnique('operasional_penyerahan_karcis_id_unique');
            });
        } catch (\Throwable $e) {
            // Ignore if index already dropped.
        }

        try {
            Schema::table('operasional', function (Blueprint $table) {
                $table->unique(['penyerahan_karcis_id', 'tahun'], 'operasional_penyerahan_tahun_unique');
            });
        } catch (\Throwable $e) {
            // Ignore if composite unique already exists.
        }
    }

    public function down(): void
    {
        try {
            Schema::table('operasional', function (Blueprint $table) {
                $table->dropUnique('operasional_penyerahan_tahun_unique');
            });
        } catch (\Throwable $e) {
            // Ignore if index does not exist.
        }

        try {
            Schema::table('operasional', function (Blueprint $table) {
                $table->unique('penyerahan_karcis_id');
            });
        } catch (\Throwable $e) {
            // Ignore if unique already exists.
        }

        Schema::table('operasional', function (Blueprint $table) {
            if (Schema::hasColumn('operasional', 'tahun')) {
                $table->dropColumn('tahun');
            }
        });
    }
};
