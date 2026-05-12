<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperasionalPetugasPeriode extends Model
{
    protected $table = 'operasional_petugas_periode';

    protected $fillable = [
        'operasional_id',
        'nama_petugas',
        'tanggal_mulai',
        'tanggal_selesai',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
        ];
    }

    public function operasional(): BelongsTo
    {
        return $this->belongsTo(Operasional::class, 'operasional_id');
    }
}
