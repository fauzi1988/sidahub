<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArsipSuratKeluar extends Model
{
    protected $table = 'arsip_surat_keluar';

    protected $primaryKey = 'id_surat_masuk';

    protected $fillable = [
        'nomor_surat',
        'tanggal_surat',
        'tanggal_terima',
        'perihal',
        'pengirim',
        'sifat_surat',
        'surat_keluar_id',
        'ringkasan',
        'lampiran',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_surat' => 'date',
            'tanggal_terima' => 'date',
            'lampiran' => 'array',
        ];
    }

    public function suratKeluar(): BelongsTo
    {
        return $this->belongsTo(SuratKeluar::class, 'surat_keluar_id', 'id_surat_keluar');
    }
}
