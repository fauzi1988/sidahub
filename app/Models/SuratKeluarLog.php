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
            'submit_ke_kabid' => 'Dikirim ke Kabid',
            'approve_kabid' => 'Disetujui Kabid',
            'revisi_kabid' => 'Revisi Substansi (Kabid)',
            'teruskan_sekretariat_ke_kadis' => 'Diteruskan ke Kadis',
            'revisi_sekretariat' => 'Revisi Administrasi (Sekretariat)',
            'approve_kadis' => 'Ditandatangani Kadis',
            'revisi_kadis' => 'Dikembalikan dari Kadis',
            'sekretariat_nomor_dan_kirim' => 'Dinomori & Dikirim',
            'arsipkan' => 'Diarsipkan',
            'batalkan' => 'Dibatalkan',
            'edit_konten' => 'Edit Konten',
        ];
    }
}

