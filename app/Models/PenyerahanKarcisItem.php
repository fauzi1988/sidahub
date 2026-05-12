<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenyerahanKarcisItem extends Model
{
    protected $table = 'penyerahan_karcis_item';

    protected $fillable = [
        'penyerahan_karcis_id',
        'karcis_kode',
        'harga_satuan',
        'lembar',
        'total',
        'uraian',
        'banyak_buku',
        'tarif',
        'nomor_seri_awal',
        'nomor_seri_akhir',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'harga_satuan' => 'decimal:2',
            'lembar' => 'integer',
            'total' => 'decimal:2',
            'banyak_buku' => 'integer',
            'tarif' => 'decimal:2',
        ];
    }

    public function penyerahan(): BelongsTo
    {
        return $this->belongsTo(PenyerahanKarcis::class, 'penyerahan_karcis_id');
    }

    public function karcis(): BelongsTo
    {
        return $this->belongsTo(Karcis::class, 'karcis_kode', 'kode_karcis');
    }
}
