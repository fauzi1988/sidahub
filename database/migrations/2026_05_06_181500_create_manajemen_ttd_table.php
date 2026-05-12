<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manajemen_ttd', function (Blueprint $table) {
            $table->id('id_ttd');
            $table->string('nama_ttd', 150);
            $table->enum('jenis_ttd', ['elektronik', 'basah', 'scan']);
            $table->string('pemilik_ttd', 150)->nullable();
            $table->string('jabatan_pemilik', 150)->nullable();
            $table->string('file_ttd', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index('jenis_ttd');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manajemen_ttd');
    }
};

