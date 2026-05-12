<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penerimaan_karcis', function (Blueprint $table) {
            $table->string('nomor_bast', 100)->after('id');
            $table->string('file_bast')->nullable()->after('total_harga');
        });
    }

    public function down(): void
    {
        Schema::table('penerimaan_karcis', function (Blueprint $table) {
            $table->dropColumn(['nomor_bast', 'file_bast']);
        });
    }
};
