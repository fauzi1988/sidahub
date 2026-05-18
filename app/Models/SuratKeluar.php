<?php

namespace App\Models;

use App\Services\SuratKeluarIsiSanitizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SuratKeluar extends Model
{
    protected $table = 'surat_keluar';

    protected $primaryKey = 'id_surat_keluar';

    protected $fillable = [
        'nomor_surat',
        'tanggal_surat',
        'tanggal_kirim',
        'perihal',
        'tujuan_surat',
        'alamat_tujuan',
        'jenis_surat',
        'sifat_surat',
        'prioritas',
        'status',
        'id_pegawai_pengusul',
        'id_pegawai_penandatangan',
        'unit_kerja',
        'created_by_user_id',
        'jenis_ttd',
        'ttd_management_id',
        'ringkasan',
        'isi_surat',
        'catatan',
        'lampiran',
        'verification_code',
        'alasan_batal',
        'submitted_at',
        'reviewed_at',
        'verified_at',
        'forwarded_to_kadis_at',
        'signed_at',
        'cancelled_at',
        'archived_at',
        'reviewed_by_kabid_user_id',
        'verified_by_sekretariat_user_id',
        'signed_by_kadis_user_id',
    ];

    public function getRouteKeyName(): string
    {
        return 'id_surat_keluar';
    }

    protected function casts(): array
    {
        return [
            'tanggal_surat' => 'date',
            'tanggal_kirim' => 'date',
            'lampiran' => 'array',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'verified_at' => 'datetime',
            'forwarded_to_kadis_at' => 'datetime',
            'signed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    public function pengusul(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'id_pegawai_pengusul', 'id_pegawai');
    }

    public function penandatangan(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'id_pegawai_penandatangan', 'id_pegawai');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function ttdManagement(): BelongsTo
    {
        return $this->belongsTo(ManajemenTtd::class, 'ttd_management_id', 'id_ttd');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(SuratKeluarLog::class, 'surat_keluar_id', 'id_surat_keluar');
    }

    public function arsipSuratKeluar(): HasOne
    {
        return $this->hasOne(ArsipSuratKeluar::class, 'surat_keluar_id', 'id_surat_keluar');
    }

    /** @deprecated Use arsipSuratKeluar() */
    public function suratMasuk(): HasOne
    {
        return $this->arsipSuratKeluar();
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'revisi_substansi', 'revisi_admin'], true);
    }

    public function isDeletable(): bool
    {
        return in_array($this->status, ['draft', 'revisi_substansi', 'revisi_admin'], true);
    }

    public function canBePrinted(): bool
    {
        return in_array($this->status, ['dikirim', 'diarsipkan'], true);
    }

    public function isiSuratDisplay(): string
    {
        return app(SuratKeluarIsiSanitizer::class)->forDisplay($this->isi_surat);
    }

    public function jenisSuratLabel(): string
    {
        return self::jenisSuratOptions()[$this->jenis_surat] ?? $this->jenis_surat;
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'draft', 'revisi_substansi', 'revisi_admin' => 'secondary',
            'menunggu_review_substansi', 'menunggu_verifikasi', 'menunggu_ttd' => 'warning',
            'disetujui' => 'info',
            'dikirim', 'diarsipkan' => 'success',
            'dibatalkan' => 'danger',
            default => 'light',
        };
    }

    /**
     * @return list<string>
     */
    public static function editableStatuses(): array
    {
        return ['draft', 'revisi_substansi', 'revisi_admin'];
    }

    public static function jenisSuratOptions(): array
    {
        return [
            'surat_dinas' => 'Surat Dinas',
            'nota_dinas' => 'Nota Dinas',
            'surat_tugas' => 'Surat Tugas',
            'undangan' => 'Undangan',
            'memo_internal' => 'Memo Internal',
            'balasan' => 'Balasan',
        ];
    }

    public static function sifatSuratOptions(): array
    {
        return [
            'biasa' => 'Biasa',
            'penting' => 'Penting',
            'segera' => 'Segera',
            'rahasia' => 'Rahasia',
        ];
    }

    public static function prioritasOptions(): array
    {
        return [
            'normal' => 'Normal',
            'tinggi' => 'Tinggi',
            'sangat_tinggi' => 'Sangat Tinggi',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            'draft' => 'Draft',
            'menunggu_review_substansi' => 'Menunggu Review Kabid',
            'revisi_substansi' => 'Revisi Substansi',
            'menunggu_verifikasi' => 'Menunggu Verifikasi Sekretariat',
            'revisi_admin' => 'Revisi Administrasi',
            'menunggu_ttd' => 'Menunggu Tanda Tangan Kadis',
            'disetujui' => 'Disetujui (Menunggu Nomor)',
            'dikirim' => 'Dikirim',
            'diarsipkan' => 'Diarsipkan',
            'dibatalkan' => 'Dibatalkan',
        ];
    }

    public static function jenisTtdOptions(): array
    {
        return [
            'elektronik' => 'TTD Elektronik',
            'basah' => 'TTD Basah',
            'scan' => 'TTD Scan',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function isiTemplates(): array
    {
        return [
            'surat_dinas' => '<p>Dengan hormat,</p><p>[Buka paragraf isi surat dinas di sini]</p><p>Demikian surat ini kami sampaikan, atas perhatian dan kerja samanya diucapkan terima kasih.</p>',
            'nota_dinas' => '<p><strong>Hal :</strong> [perihal]</p><p>Kepada Yth. [nama penerima internal]</p><p>[Buka isi nota dinas]</p><p>Demikian untuk menjadi perhatian dan arahan Bapak/Ibu.</p>',
            'surat_tugas' => '<p><strong>Dasar:</strong></p><ol><li>[dasar hukum/keputusan]</li></ol><p><strong>Memberikan tugas kepada:</strong></p><table><tbody><tr><td>Nama</td><td>:</td><td></td></tr><tr><td>NIP</td><td>:</td><td></td></tr><tr><td>Jabatan</td><td>:</td><td></td></tr></tbody></table><p><strong>Untuk melaksanakan:</strong> [uraian tugas]</p><p>Pelaksanaan tugas pada tanggal [tanggal].</p>',
            'undangan' => '<p>Mengharap kehadiran Bapak/Ibu pada:</p><table><tbody><tr><td>Hari/Tanggal</td><td>:</td><td></td></tr><tr><td>Waktu</td><td>:</td><td></td></tr><tr><td>Tempat</td><td>:</td><td></td></tr><tr><td>Acara</td><td>:</td><td></td></tr></tbody></table><p>Demikian undangan ini disampaikan.</p>',
            'memo_internal' => '<p>Kepada seluruh unit kerja terkait,</p><p>[Buka isi memo internal]</p><p>Mohon dipedomani dan dilaksanakan.</p>',
            'balasan' => '<p>Menindaklanjuti surat [nomor/tanggal surat masuk] perihal [perihal], dengan hormat kami sampaikan hal sebagai berikut:</p><p>[Buka isi balasan]</p>',
        ];
    }

    public function scopeForInboxOperator(Builder $query, ?int $idPegawai, ?int $userId): Builder
    {
        if (! $idPegawai && ! $userId) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($idPegawai, $userId) {
            if ($idPegawai) {
                $q->where('id_pegawai_pengusul', $idPegawai);
            }
            if ($userId) {
                $q->orWhere('created_by_user_id', $userId);
            }
        });
    }

    public function scopeForUnitKerja(Builder $query, ?string $unitKerja): Builder
    {
        if (! $unitKerja) {
            return $query;
        }

        return $query->where('unit_kerja', $unitKerja);
    }
}
