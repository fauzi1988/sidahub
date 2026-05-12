<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanMingguan extends Model
{
    protected $table = 'laporan_mingguan';

    protected $fillable = [
        'tanggal',
        'tahun',
        'nama_petugas',
        'tempat_tugas',
        'nama_karcis',
        'harga_satuan',
        'jumlah_karcis',
        'lembar_terjual',
        'total_penjualan',
        'setor_kada',
        'tanggal_setor',
        'ket',
        'minggu_ke',
        'bukti_setor',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'tahun' => 'integer',
            'harga_satuan' => 'decimal:2',
            'jumlah_karcis' => 'integer',
            'lembar_terjual' => 'integer',
            'total_penjualan' => 'decimal:2',
            'setor_kada' => 'decimal:2',
            'tanggal_setor' => 'date',
            'minggu_ke' => 'integer',
        ];
    }
}
