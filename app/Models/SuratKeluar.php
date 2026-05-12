<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'jenis_ttd',
        'ttd_management_id',
        'ringkasan',
        'isi_surat',
        'catatan',
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

    public function ttdManagement(): BelongsTo
    {
        return $this->belongsTo(ManajemenTtd::class, 'ttd_management_id', 'id_ttd');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(SuratKeluarLog::class, 'surat_keluar_id', 'id_surat_keluar');
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
            'menunggu_verifikasi' => 'Menunggu Verifikasi',
            'revisi_admin' => 'Revisi Admin',
            'menunggu_review_substansi' => 'Menunggu Review Substansi',
            'revisi_substansi' => 'Revisi Substansi',
            'menunggu_paraf' => 'Menunggu Paraf',
            'menunggu_ttd' => 'Menunggu Tanda Tangan',
            'disetujui' => 'Disetujui',
            'dikirim' => 'Dikirim',
            'diarsipkan' => 'Diarsipkan',
            'dibatalkan' => 'Dibatalkan',
        ];
    }

    public function canBePrinted(): bool
    {
        return in_array($this->status, ['dikirim', 'diarsipkan'], true);
    }

    public static function jenisTtdOptions(): array
    {
        return [
            'elektronik' => 'TTD Elektronik',
            'basah' => 'TTD Basah',
            'scan' => 'TTD Scan',
        ];
    }
}
