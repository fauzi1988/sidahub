<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penyerahan_karcis_item', function (Blueprint $table) {
            $table->string('karcis_kode', 30)->nullable()->after('penyerahan_karcis_id');
            $table->decimal('harga_satuan', 15, 2)->nullable()->after('karcis_kode');
            $table->unsignedInteger('lembar')->nullable()->after('harga_satuan');
            $table->decimal('total', 18, 2)->nullable()->after('lembar');

            $table->foreign('karcis_kode')
                ->references('kode_karcis')
                ->on('karcis')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('penyerahan_karcis_item', function (Blueprint $table) {
            $table->dropForeign(['karcis_kode']);
            $table->dropColumn(['karcis_kode', 'harga_satuan', 'lembar', 'total']);
        });
    }
};
