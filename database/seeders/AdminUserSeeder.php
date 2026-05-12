<?php

namespace Database\Seeders;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $pegawai = Pegawai::query()->firstOrCreate(
            ['nik' => '6401050105060001'],
            [
                'nip' => null,
                'nama_lengkap' => 'Administrator',
                'gelar_depan' => null,
                'gelar_belakang' => null,
                'tempat_lahir' => 'Samarinda',
                'tanggal_lahir' => '1990-01-01',
                'jenis_kelamin' => 'L',
                'agama' => 'Islam',
                'status_kepegawaian' => 'PNS',
                'alamat_ktp' => 'Kalimantan Timur',
                'no_hp' => '080000000000',
                'email_dinas' => 'admin@dishub.test',
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'admin@dishub.test'],
            [
                'id_pegawai' => $pegawai->id_pegawai,
                'name' => 'Administrator',
                'password' => 'AdminHaltim2026!',
                'is_super_admin' => true,
            ]
        );
    }
}
