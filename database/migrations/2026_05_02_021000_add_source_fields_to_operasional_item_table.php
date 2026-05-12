<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operasional_item', function (Blueprint $table) {
            if (! Schema::hasColumn('operasional_item', 'source_penyerahan_karcis_id')) {
                $table->unsignedBigInteger('source_penyerahan_karcis_id')->nullable()->after('operasional_id');
            }
            if (! Schema::hasColumn('operasional_item', 'source_nomor_bast')) {
                $table->string('source_nomor_bast', 120)->nullable()->after('source_penyerahan_karcis_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('operasional_item', function (Blueprint $table) {
            if (Schema::hasColumn('operasional_item', 'source_nomor_bast')) {
                $table->dropColumn('source_nomor_bast');
            }
            if (Schema::hasColumn('operasional_item', 'source_penyerahan_karcis_id')) {
                $table->dropColumn('source_penyerahan_karcis_id');
            }
        });
    }
};
