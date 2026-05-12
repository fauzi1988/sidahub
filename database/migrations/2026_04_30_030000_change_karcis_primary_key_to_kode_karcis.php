<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('karcis', 'kode_karcis')) {
            Schema::table('karcis', function (Blueprint $table) {
                $table->string('kode_karcis', 30)->nullable()->after('id');
            });
        }

        DB::table('karcis')
            ->orderBy('id')
            ->get(['id'])
            ->each(function ($row): void {
                DB::table('karcis')
                    ->where('id', $row->id)
                    ->update([
                        'kode_karcis' => 'KRC-'.str_pad((string) $row->id, 4, '0', STR_PAD_LEFT),
                    ]);
            });

        try {
            Schema::table('karcis', function (Blueprint $table) {
                $table->unique('kode_karcis');
            });
        } catch (\Throwable $e) {
            // already exists
        }

        if (! Schema::hasColumn('penerimaan_karcis', 'karcis_kode')) {
            try {
                Schema::table('penerimaan_karcis', function (Blueprint $table) {
                    $table->dropForeign(['karcis_id']);
                });
            } catch (\Throwable $e) {
                // foreign key may not exist
            }

            Schema::table('penerimaan_karcis', function (Blueprint $table) {
                $table->string('karcis_kode', 30)->nullable()->after('nomor_bast');
            });

            DB::statement('
                UPDATE penerimaan_karcis pk
                JOIN karcis k ON k.id = pk.karcis_id
                SET pk.karcis_kode = k.kode_karcis
            ');

            Schema::table('penerimaan_karcis', function (Blueprint $table) {
                $table->dropColumn('karcis_id');
            });
        }

        DB::statement('ALTER TABLE penerimaan_karcis MODIFY karcis_kode VARCHAR(30) NOT NULL');
        try {
            Schema::table('penerimaan_karcis', function (Blueprint $table) {
                $table->foreign('karcis_kode')->references('kode_karcis')->on('karcis')->cascadeOnDelete();
            });
        } catch (\Throwable $e) {
            // already exists
        }

        if (Schema::hasColumn('karcis', 'id')) {
            DB::statement('ALTER TABLE karcis MODIFY id BIGINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE karcis DROP PRIMARY KEY');
            DB::statement('ALTER TABLE karcis DROP COLUMN id');
        }

        DB::statement('ALTER TABLE karcis MODIFY kode_karcis VARCHAR(30) NOT NULL');
        try {
            DB::statement('ALTER TABLE karcis ADD PRIMARY KEY (kode_karcis)');
        } catch (\Throwable $e) {
            // already primary key
        }
    }

    public function down(): void
    {
        Schema::table('karcis', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->first();
        });

        DB::statement('ALTER TABLE karcis DROP PRIMARY KEY');
        DB::statement('ALTER TABLE karcis ADD PRIMARY KEY (id)');
        DB::statement('ALTER TABLE karcis MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');

        Schema::table('penerimaan_karcis', function (Blueprint $table) {
            $table->dropForeign(['karcis_kode']);
            $table->unsignedBigInteger('karcis_id')->nullable()->after('nomor_bast');
        });

        DB::statement('
            UPDATE penerimaan_karcis pk
            JOIN karcis k ON k.kode_karcis = pk.karcis_kode
            SET pk.karcis_id = k.id
        ');
        DB::statement('ALTER TABLE penerimaan_karcis MODIFY karcis_id BIGINT UNSIGNED NOT NULL');

        Schema::table('penerimaan_karcis', function (Blueprint $table) {
            $table->foreign('karcis_id')->references('id')->on('karcis')->cascadeOnDelete();
            $table->dropColumn('karcis_kode');
        });

        Schema::table('karcis', function (Blueprint $table) {
            $table->dropUnique(['kode_karcis']);
            $table->dropColumn('kode_karcis');
        });
    }
};
