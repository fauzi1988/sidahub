<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operasional_item', function (Blueprint $table) {
            $table->decimal('total_penjualan', 18, 2)->nullable()->after('total_terjual');
        });
    }

    public function down(): void
    {
        Schema::table('operasional_item', function (Blueprint $table) {
            $table->dropColumn('total_penjualan');
        });
    }
};
