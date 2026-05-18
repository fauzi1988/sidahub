<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratMasukLog extends Model
{
    protected $table = 'surat_masuk_logs';

    protected $fillable = [
        'surat_masuk_id',
        'user_id',
        'action',
        'from_status',
        'to_status',
        'note',
    ];

    public function suratMasuk(): BelongsTo
    {
        return $this->belongsTo(SuratMasuk::class, 'surat_masuk_id', 'id_surat_masuk');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function actionLabel(): string
    {
        return self::actionLabels()[$this->action] ?? $this->action;
    }

    /**
     * @return array<string, string>
     */
    public static function actionLabels(): array
    {
        return [
            'catat' => 'Pencatatan',
            'agenda' => 'Agenda Masuk',
            'teruskan_kadis' => 'Teruskan ke Kadis',
            'disposisi_kabid_unit' => 'Disposisi Kabid/Unit',
            'tindak_lanjut' => 'Tindak Lanjut',
            'selesai' => 'Selesai',
            'selesai_disposisi' => 'Selesai (satu disposisi)',
            'arsipkan' => 'Diarsipkan',
            'batalkan' => 'Dibatalkan',
            'edit' => 'Edit Data',
        ];
    }
}
