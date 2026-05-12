<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DokumenPegawai extends Model
{
    protected $table = 'dokumen_pegawai';

    protected $primaryKey = 'id_dokumen';

    protected $fillable = [
        'id_pegawai',
        'nama_dokumen',
        'file_dokumen',
    ];

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'id_pegawai', 'id_pegawai');
    }
}
