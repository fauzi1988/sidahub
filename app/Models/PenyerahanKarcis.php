<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PenyerahanKarcis extends Model
{
    protected $table = 'penyerahan_karcis';

    protected $fillable = [
        'nomor_bast',
        'tanggal',
        'pihak_pertama_id_pegawai',
        'pihak_kedua_id_pegawai',
        'pihak_pertama_nama',
        'pihak_pertama_nip',
        'pihak_pertama_jabatan',
        'pihak_pertama_instansi',
        'pihak_pertama_alamat',
        'pihak_kedua_nama',
        'pihak_kedua_nip',
        'pihak_kedua_jabatan',
        'pihak_kedua_tempat_tugas',
        'pihak_kedua_instansi',
        'pihak_kedua_alamat',
        'mengetahui_nama',
        'mengetahui_nip',
        'file_surat',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function pihakPertamaPegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pihak_pertama_id_pegawai', 'id_pegawai');
    }

    public function pihakKeduaPegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pihak_kedua_id_pegawai', 'id_pegawai');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PenyerahanKarcisItem::class, 'penyerahan_karcis_id');
    }

    public function operasional()
    {
        return $this->hasOne(Operasional::class, 'penyerahan_karcis_id');
    }

    public function operasionals(): HasMany
    {
        return $this->hasMany(Operasional::class, 'penyerahan_karcis_id');
    }
}
