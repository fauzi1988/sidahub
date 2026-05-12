<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendidikanPegawai extends Model
{
    protected $table = 'pendidikan_pegawai';

    protected $primaryKey = 'id_pendidikan';

    public function getRouteKeyName(): string
    {
        return 'id_pendidikan';
    }

    protected $fillable = [
        'id_pegawai',
        'tingkat',
        'jurusan',
        'nama_institusi',
        'tahun_lulus',
    ];

    protected function casts(): array
    {
        return [
            'tahun_lulus' => 'integer',
        ];
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'id_pegawai', 'id_pegawai');
    }
}
