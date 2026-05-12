<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE operasional MODIFY bukti_setor VARCHAR(255) NULL');

        Schema::table('operasional_item', function (Blueprint $table) {
            $table->string('bukti_setor')->nullable()->after('sisa_lembar');
        });
    }

    public function down(): void
    {
        Schema::table('operasional_item', function (Blueprint $table) {
            $table->dropColumn('bukti_setor');
        });
    }
};
