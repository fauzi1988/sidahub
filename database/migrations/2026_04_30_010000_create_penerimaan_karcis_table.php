<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penerimaan_karcis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karcis_id')->constrained('karcis')->cascadeOnDelete();
            $table->decimal('harga_satuan', 15, 2);
            $table->unsignedInteger('stock_masuk');
            $table->unsignedBigInteger('total_stock');
            $table->decimal('total_harga', 18, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penerimaan_karcis');
    }
};
