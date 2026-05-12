<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operasional', function (Blueprint $table) {
            if (! Schema::hasColumn('operasional', 'tempat_tugas')) {
                $table->string('tempat_tugas', 150)->nullable()->after('nama_penanggungjawab');
            }
        });
    }

    public function down(): void
    {
        Schema::table('operasional', function (Blueprint $table) {
            if (Schema::hasColumn('operasional', 'tempat_tugas')) {
                $table->dropColumn('tempat_tugas');
            }
        });
    }
};
