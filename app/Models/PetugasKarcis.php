<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PetugasKarcis extends Model
{
    protected $table = 'petugas_karcis';

    protected $fillable = [
        'nomor_induk_pegawai',
        'nama_pegawai',
        'alamat',
        'nomor_telepon',
        'status',
        'instansi',
        'tempat_tugas',
        'foto',
    ];
}
