<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ManajemenTtd extends Model
{
    protected $table = 'manajemen_ttd';

    protected $primaryKey = 'id_ttd';

    protected $fillable = [
        'nama_ttd',
        'jenis_ttd',
        'pemilik_ttd',
        'jabatan_pemilik',
        'file_ttd',
        'is_active',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function suratKeluar(): HasMany
    {
        return $this->hasMany(SuratKeluar::class, 'ttd_management_id', 'id_ttd');
    }
}

