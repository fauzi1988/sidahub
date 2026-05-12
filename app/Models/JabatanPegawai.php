<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JabatanPegawai extends Model
{
    protected $table = 'jabatan_pegawai';

    protected $primaryKey = 'id_jabatan';

    public function getRouteKeyName(): string
    {
        return 'id_jabatan';
    }

    protected $fillable = [
        'id_pegawai',
        'instansi',
        'jabatan',
        'unit_kerja',
        'karpeg',
        'pangkat_golongan',
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
        'tmt',
        'status_jabatan',
    ];

    protected function casts(): array
    {
        return [
            'tmt' => 'date',
            'tmt_berkala_terakhir' => 'date',
            'tmt_cpnsd' => 'date',
            'tmt_pns' => 'date',
        ];
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'id_pegawai', 'id_pegawai');
    }
}
