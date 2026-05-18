<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratMasukDisposisi extends Model
{
    protected $table = 'surat_masuk_disposisi';

    protected $fillable = [
        'surat_masuk_id',
        'from_user_id',
        'to_pegawai_id',
        'to_unit_kerja',
        'tingkat',
        'instruksi',
        'batas_waktu',
        'status',
        'catatan_tindak_lanjut',
        'selesai_at',
    ];

    protected function casts(): array
    {
        return [
            'batas_waktu' => 'date',
            'selesai_at' => 'datetime',
        ];
    }

    public function suratMasuk(): BelongsTo
    {
        return $this->belongsTo(SuratMasuk::class, 'surat_masuk_id', 'id_surat_masuk');
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toPegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'to_pegawai_id', 'id_pegawai');
    }

    public function tingkatLabel(): string
    {
        return match ($this->tingkat) {
            'kadis' => 'Kadis',
            'kabid' => 'Kabid',
            default => 'Unit',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'aktif' => 'Aktif',
            'selesai' => 'Selesai',
            'dibatalkan' => 'Dibatalkan',
            default => $this->status,
        };
    }

    public function isOverdue(): bool
    {
        return $this->status === 'aktif'
            && $this->batas_waktu
            && $this->batas_waktu->isPast();
    }

    public function targetLabel(): string
    {
        if ($this->relationLoaded('toPegawai') && $this->toPegawai) {
            return $this->toPegawai->nama_lengkap;
        }

        if ($this->to_unit_kerja) {
            return 'Unit: '.$this->to_unit_kerja;
        }

        return '-';
    }
}
