<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SuratMasuk extends Model
{
    protected $table = 'surat_masuk';

    protected $primaryKey = 'id_surat_masuk';

    protected $fillable = [
        'nomor_agenda',
        'nomor_surat_pengirim',
        'tanggal_surat',
        'tanggal_terima',
        'perihal',
        'pengirim',
        'sifat_surat',
        'ringkasan',
        'lampiran',
        'status',
        'created_by_user_id',
        'surat_keluar_balasan_id',
        'agenda_at',
        'forwarded_to_kadis_at',
        'disposed_at',
        'completed_at',
        'archived_at',
        'cancelled_at',
        'alasan_batal',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_surat' => 'date',
            'tanggal_terima' => 'date',
            'lampiran' => 'array',
            'agenda_at' => 'datetime',
            'forwarded_to_kadis_at' => 'datetime',
            'disposed_at' => 'datetime',
            'completed_at' => 'datetime',
            'archived_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function suratKeluarBalasan(): BelongsTo
    {
        return $this->belongsTo(SuratKeluar::class, 'surat_keluar_balasan_id', 'id_surat_keluar');
    }

    public function disposisi(): HasMany
    {
        return $this->hasMany(SuratMasukDisposisi::class, 'surat_masuk_id', 'id_surat_masuk');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(SuratMasukLog::class, 'surat_masuk_id', 'id_surat_masuk');
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? $this->status;
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'tercatat' => 'secondary',
            'agenda' => 'info',
            'menunggu_disposisi_kadis' => 'warning',
            'disposisi_ke_unit', 'proses' => 'primary',
            'selesai' => 'success',
            'diarsipkan' => 'dark',
            'dibatalkan' => 'danger',
            default => 'light',
        };
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['tercatat', 'agenda'], true);
    }

    public function hasActiveDisposisi(): bool
    {
        return $this->disposisi()->where('status', 'aktif')->exists();
    }

    public function disposisiProgress(): string
    {
        $total = $this->disposisi()->count();
        if ($total === 0) {
            return '-';
        }

        $selesai = $this->disposisi()->where('status', 'selesai')->count();

        return $selesai.' / '.$total.' selesai';
    }

    public function hasOverdueDisposisi(): bool
    {
        return $this->disposisi()
            ->where('status', 'aktif')
            ->whereNotNull('batas_waktu')
            ->whereDate('batas_waktu', '<', now()->toDateString())
            ->exists();
    }

    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return [
            'tercatat' => 'Tercatat',
            'agenda' => 'Agenda',
            'menunggu_disposisi_kadis' => 'Menunggu Disposisi Kadis',
            'disposisi_ke_unit' => 'Disposisi ke Kabid/Unit',
            'proses' => 'Proses Tindak Lanjut',
            'selesai' => 'Selesai',
            'diarsipkan' => 'Diarsipkan',
            'dibatalkan' => 'Dibatalkan',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function sifatSuratOptions(): array
    {
        return SuratKeluar::sifatSuratOptions();
    }
}
