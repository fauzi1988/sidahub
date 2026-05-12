<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jabatan_pegawai', function (Blueprint $table) {
            $table->string('karpeg', 100)->nullable()->after('unit_kerja');
            $table->unsignedTinyInteger('masa_kerja_gol_tahun')->nullable()->after('pangkat_golongan');
            $table->unsignedTinyInteger('masa_kerja_gol_bulan')->nullable()->after('masa_kerja_gol_tahun');
            $table->string('pelatihan_pim_i', 100)->nullable()->after('masa_kerja_gol_bulan');
            $table->string('pelatihan_pim_ii', 100)->nullable()->after('pelatihan_pim_i');
            $table->string('pelatihan_pim_iii', 100)->nullable()->after('pelatihan_pim_ii');
            $table->string('pelatihan_pim_iv', 100)->nullable()->after('pelatihan_pim_iii');
            $table->unsignedInteger('jlh_jam')->nullable()->after('pelatihan_pim_iv');
            $table->unsignedTinyInteger('masa_kerja_sel_tahun')->nullable()->after('jlh_jam');
            $table->unsignedTinyInteger('masa_kerja_sel_bulan')->nullable()->after('masa_kerja_sel_tahun');
            $table->date('tmt_berkala_terakhir')->nullable()->after('masa_kerja_sel_bulan');
            $table->date('tmt_cpnsd')->nullable()->after('tmt_berkala_terakhir');
            $table->date('tmt_pns')->nullable()->after('tmt_cpnsd');
            $table->string('ket', 500)->nullable()->after('tmt_pns');
        });
    }

    public function down(): void
    {
        Schema::table('jabatan_pegawai', function (Blueprint $table) {
            $table->dropColumn([
                'karpeg',
                'masa_kerja_gol_tahun',
                'masa_kerja_gol_bulan',
                'pelatihan_pim_i',
                'pelatihan_pim_ii',
                'pelatihan_pim_iii',
                'pelatihan_pim_iv',
                'jlh_jam',
                'masa_kerja_sel_tahun',
                'masa_kerja_sel_bulan',
                'tmt_berkala_terakhir',
                'tmt_cpnsd',
                'tmt_pns',
                'ket',
            ]);
        });
    }
};
