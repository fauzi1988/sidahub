<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratKeluarLog extends Model
{
    protected $table = 'surat_keluar_logs';

    protected $fillable = [
        'surat_keluar_id',
        'user_id',
        'action',
        'from_status',
        'to_status',
        'note',
    ];

    public function surat(): BelongsTo
    {
        return $this->belongsTo(SuratKeluar::class, 'surat_keluar_id', 'id_surat_keluar');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

