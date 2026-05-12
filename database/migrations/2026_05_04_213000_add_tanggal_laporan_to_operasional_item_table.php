<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operasional_item', function (Blueprint $table) {
            if (! Schema::hasColumn('operasional_item', 'tanggal_laporan')) {
                $table->date('tanggal_laporan')->nullable()->after('source_nomor_bast');
            }
        });
    }

    public function down(): void
    {
        Schema::table('operasional_item', function (Blueprint $table) {
            if (Schema::hasColumn('operasional_item', 'tanggal_laporan')) {
                $table->dropColumn('tanggal_laporan');
            }
        });
    }
};
