<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperasionalItem extends Model
{
    protected $table = 'operasional_item';

    protected $fillable = [
        'operasional_id',
        'source_penyerahan_karcis_id',
        'source_nomor_bast',
        'tanggal_laporan',
        'nama_petugas',
        'karcis_kode',
        'nama_karcis',
        'harga_satuan',
        'lembar',
        'total',
        'lembar_terjual',
        'total_terjual',
        'total_penjualan',
        'sisa_lembar',
        'bukti_setor',
    ];

    protected function casts(): array
    {
        return [
            'harga_satuan' => 'decimal:2',
            'lembar' => 'integer',
            'total' => 'decimal:2',
            'lembar_terjual' => 'integer',
            'total_terjual' => 'decimal:2',
            'total_penjualan' => 'decimal:2',
            'sisa_lembar' => 'integer',
            'source_penyerahan_karcis_id' => 'integer',
            'tanggal_laporan' => 'date',
        ];
    }

    public function operasional(): BelongsTo
    {
        return $this->belongsTo(Operasional::class, 'operasional_id');
    }
}
