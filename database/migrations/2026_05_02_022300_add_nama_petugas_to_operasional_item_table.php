<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operasional_item', function (Blueprint $table) {
            if (! Schema::hasColumn('operasional_item', 'nama_petugas')) {
                $table->string('nama_petugas', 150)->nullable()->after('source_nomor_bast');
            }
        });

        DB::statement('UPDATE operasional_item oi JOIN operasional o ON o.id = oi.operasional_id SET oi.nama_petugas = o.nama_penanggungjawab WHERE oi.nama_petugas IS NULL');
    }

    public function down(): void
    {
        Schema::table('operasional_item', function (Blueprint $table) {
            if (Schema::hasColumn('operasional_item', 'nama_petugas')) {
                $table->dropColumn('nama_petugas');
            }
        });
    }
};
