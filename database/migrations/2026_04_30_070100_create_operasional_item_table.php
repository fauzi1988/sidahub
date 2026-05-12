<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operasional_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operasional_id')->constrained('operasional')->cascadeOnDelete();
            $table->string('karcis_kode', 30)->nullable();
            $table->string('nama_karcis', 255);
            $table->decimal('harga_satuan', 15, 2);
            $table->unsignedInteger('lembar');
            $table->decimal('total', 18, 2);
            $table->unsignedInteger('lembar_terjual');
            $table->decimal('total_terjual', 18, 2);
            $table->unsignedInteger('sisa_lembar');
            $table->timestamps();

            $table->foreign('karcis_kode')
                ->references('kode_karcis')
                ->on('karcis')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operasional_item');
    }
};
