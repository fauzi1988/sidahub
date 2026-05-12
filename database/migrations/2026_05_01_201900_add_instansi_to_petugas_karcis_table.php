<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('petugas_karcis', function (Blueprint $table) {
            if (! Schema::hasColumn('petugas_karcis', 'instansi')) {
                $table->string('instansi', 200)->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('petugas_karcis', function (Blueprint $table) {
            if (Schema::hasColumn('petugas_karcis', 'instansi')) {
                $table->dropColumn('instansi');
            }
        });
    }
};
