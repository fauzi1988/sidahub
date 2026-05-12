<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operasional_petugas_periode', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operasional_id')->constrained('operasional')->cascadeOnDelete();
            $table->string('nama_petugas', 150);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operasional_petugas_periode');
    }
};
