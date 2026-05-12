<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenerimaanKarcis extends Model
{
    protected $table = 'penerimaan_karcis';

    protected $fillable = [
        'nomor_bast',
        'karcis_kode',
        'harga_satuan',
        'stock_masuk',
        'total_stock',
        'total_harga',
        'file_bast',
    ];

    protected function casts(): array
    {
        return [
            'harga_satuan' => 'decimal:2',
            'stock_masuk' => 'integer',
            'total_stock' => 'integer',
            'total_harga' => 'decimal:2',
        ];
    }

    public function karcis(): BelongsTo
    {
        return $this->belongsTo(Karcis::class, 'karcis_kode', 'kode_karcis');
    }
}
