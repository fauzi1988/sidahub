<?php

/**
 * Hierarki modul → sub menu untuk UI sidebar & checklist manajemen akun.
 * Key permission dipakai di middleware `permission:key` dan disimpan di user_permissions.
 */
return [

    'modules' => [
        [
            'key' => 'dashboard',
            'label' => 'Dashboard',
            'icon' => 'fa-dashboard yellow_color',
            'route' => 'dashboard',
            'items' => [],
        ],
        [
            'key' => 'kepegawaian',
            'label' => 'Kepegawaian',
            'icon' => 'fa-users purple_color',
            'items' => [
                ['key' => 'kepegawaian.pegawai', 'label' => 'Data Pegawai', 'route' => 'pegawai.index'],
                ['key' => 'kepegawaian.pendidikan', 'label' => 'Pendidikan', 'route' => 'pendidikan.index'],
                ['key' => 'kepegawaian.persuratan.surat_keluar', 'label' => 'Persuratan - Surat Keluar', 'route' => 'persuratan-surat-keluar.index'],
                ['key' => 'kepegawaian.persuratan.approve_kabid', 'label' => 'Persuratan - Approve Kabid', 'route' => 'persuratan-surat-keluar.approve-kabid'],
                ['key' => 'kepegawaian.persuratan.approve_sekretariat', 'label' => 'Persuratan - Approve Sekretariat', 'route' => 'persuratan-surat-keluar.approve-sekretariat'],
                ['key' => 'kepegawaian.persuratan.approve_kadis', 'label' => 'Persuratan - Approve Kadis', 'route' => 'persuratan-surat-keluar.approve-kadis'],
                ['key' => 'kepegawaian.persuratan.manajemen_ttd', 'label' => 'Persuratan - Manajemen TTD', 'route' => 'manajemen-ttd.index'],
                ['key' => 'kepegawaian.perencanaan', 'label' => 'Perencanaan', 'route' => null],
            ],
        ],
        [
            'key' => 'retribusi',
            'label' => 'Retribusi',
            'icon' => 'fa-money purple_color',
            'items' => [
                ['key' => 'retribusi.pelabuhan', 'label' => 'Pelabuhan', 'route' => 'retribusi.pelabuhan'],
                ['key' => 'retribusi.karcis', 'label' => 'Karcis', 'route' => 'karcis.index'],
                ['key' => 'retribusi.terima_karcis', 'label' => 'Terima Karcis', 'route' => 'terima-karcis.index'],
                ['key' => 'retribusi.penyerahan_karcis', 'label' => 'Penyerahan Karcis', 'route' => 'penyerahan-karcis.index'],
                ['key' => 'retribusi.operasional', 'label' => 'Operasional', 'route' => 'operasional.index'],
                ['key' => 'retribusi.laporan_harian', 'label' => 'Laporan Harian', 'route' => 'laporan-harian.index'],
                ['key' => 'retribusi.laporan_mingguan', 'label' => 'Laporan Mingguan', 'route' => 'laporan-mingguan.index'],
                ['key' => 'retribusi.petugas_karcis', 'label' => 'Petugas Karcis', 'route' => 'petugas-karcis.index'],
            ],
        ],
        [
            'key' => 'llaj',
            'label' => 'LLAJ',
            'icon' => 'fa-car purple_color',
            'items' => [
                ['key' => 'llaj.kendaraan', 'label' => 'Kendaraan', 'route' => null],
                ['key' => 'llaj.kir', 'label' => 'KIR', 'route' => null],
                ['key' => 'llaj.laka_lantas', 'label' => 'Laka Lantas', 'route' => null],
            ],
        ],
        [
            'key' => 'sarpras',
            'label' => 'Sarpras',
            'icon' => 'fa-wrench purple_color',
            'items' => [
                ['key' => 'sarpras.pelabuhan', 'label' => 'Pelabuhan', 'route' => null],
                ['key' => 'sarpras.rambu', 'label' => 'Rambu', 'route' => null],
                ['key' => 'sarpras.apill', 'label' => 'APILL', 'route' => null],
                ['key' => 'sarpras.alat_teknis', 'label' => 'Alat Teknis', 'route' => null],
                ['key' => 'sarpras.lapor_kerusakan', 'label' => 'Lapor Kerusakan', 'route' => null],
            ],
        ],
        [
            'key' => 'informasi',
            'label' => 'Informasi',
            'icon' => 'fa-info-circle purple_color',
            'items' => [
                ['key' => 'informasi.berita', 'label' => 'Berita', 'route' => null],
                ['key' => 'informasi.pengumuman', 'label' => 'Pengumuman', 'route' => null],
            ],
        ],
        [
            'key' => 'pengaturan',
            'label' => 'Pengaturan',
            'icon' => 'fa-cog purple_color',
            'items' => [
                ['key' => 'pengaturan.pengguna', 'label' => 'Manajemen Akun', 'route' => 'pengguna.index'],
            ],
        ],
    ],

];
