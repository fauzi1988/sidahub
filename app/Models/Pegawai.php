<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pegawai extends Model
{
    protected $table = 'pegawai';

    protected $primaryKey = 'id_pegawai';

    protected $fillable = [
        'nip',
        'nik',
        'nama_lengkap',
        'gelar_depan',
        'gelar_belakang',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'agama',
        'status_kepegawaian',
        'alamat_ktp',
        'no_hp',
        'email_dinas',
        'foto',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
        ];
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id_pegawai', 'id_pegawai');
    }

    public function jabatanPegawai(): HasMany
    {
        return $this->hasMany(JabatanPegawai::class, 'id_pegawai', 'id_pegawai');
    }

    public function pendidikanPegawai(): HasMany
    {
        return $this->hasMany(PendidikanPegawai::class, 'id_pegawai', 'id_pegawai');
    }

    public function dokumenPegawai(): HasMany
    {
        return $this->hasMany(DokumenPegawai::class, 'id_pegawai', 'id_pegawai');
    }
}
