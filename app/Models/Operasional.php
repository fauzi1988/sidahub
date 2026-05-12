<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Operasional extends Model
{
    protected $table = 'operasional';

    protected $fillable = [
        'penyerahan_karcis_id',
        'tanggal',
        'tahun',
        'nomor_bast',
        'nama_penanggungjawab',
        'tempat_tugas',
        'bukti_setor',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'tahun' => 'integer',
        ];
    }

    public function penyerahan(): BelongsTo
    {
        return $this->belongsTo(PenyerahanKarcis::class, 'penyerahan_karcis_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OperasionalItem::class, 'operasional_id');
    }

    public function petugasPeriode(): HasMany
    {
        return $this->hasMany(OperasionalPetugasPeriode::class, 'operasional_id');
    }
}
