<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DokumenPegawaiController;
use App\Http\Controllers\JabatanPegawaiController;
use App\Http\Controllers\KarcisController;
use App\Http\Controllers\LaporanHarianController;
use App\Http\Controllers\LaporanMingguanController;
use App\Http\Controllers\ManajemenTtdController;
use App\Http\Controllers\OperasionalController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\PenyerahanKarcisController;
use App\Http\Controllers\PenerimaanKarcisController;
use App\Http\Controllers\PetugasKarcisController;
use App\Http\Controllers\PersuratanController;
use App\Http\Controllers\ArsipSuratKeluarController;
use App\Http\Controllers\SuratMasukController;
use App\Http\Controllers\PendidikanController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserAccountController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect('/admin/dashboard')
        : redirect()->route('login');
});

Route::get('/verifikasi/surat-keluar/{code}', [PersuratanController::class, 'verify'])
    ->name('persuratan-surat-keluar.verify');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])
        ->name('notifications.read-all');

    Route::redirect('/admin', '/admin/dashboard');
    Route::view('/admin/dashboard', 'admin.dashboard')->name('dashboard');

    Route::middleware('permission:pengaturan.pengguna')->group(function () {
        Route::resource('/admin/pengaturan/pengguna', UserAccountController::class)
            ->except(['show'])
            ->names([
                'index' => 'pengguna.index',
                'create' => 'pengguna.create',
                'store' => 'pengguna.store',
                'edit' => 'pengguna.edit',
                'update' => 'pengguna.update',
                'destroy' => 'pengguna.destroy',
            ]);
    });

    Route::view('/admin/retribusi/pelabuhan', 'admin.Retribusi.pelabuhan.index')
        ->middleware('permission:retribusi.pelabuhan')
        ->name('retribusi.pelabuhan');

    Route::middleware('permission:retribusi.karcis')->group(function () {
        Route::resource('/admin/retribusi/karcis', KarcisController::class)
            ->parameters(['karcis' => 'karcis'])
            ->names('karcis');
    });

    Route::middleware('permission:retribusi.terima_karcis')->group(function () {
        Route::resource('/admin/retribusi/terima_karcis', PenerimaanKarcisController::class)
            ->parameters(['terima_karcis' => 'terima_karci'])
            ->names('terima-karcis');
    });

    Route::middleware('permission:retribusi.penyerahan_karcis')->group(function () {
        Route::get('/admin/retribusi/penyerahan_karcis/{penyerahan_karci}/print', [PenyerahanKarcisController::class, 'print'])
            ->name('penyerahan-karcis.print');
        Route::resource('/admin/retribusi/penyerahan_karcis', PenyerahanKarcisController::class)
            ->parameters(['penyerahan_karcis' => 'penyerahan_karci'])
            ->names('penyerahan-karcis');
    });

    Route::middleware('permission:retribusi.operasional')->group(function () {
        Route::resource('/admin/retribusi/operasional', OperasionalController::class)
            ->parameters(['operasional' => 'operasional'])
            ->names('operasional');
        Route::get('/admin/retribusi/operasional-item/{operasional_item}/edit', [OperasionalController::class, 'editItem'])
            ->name('operasional-item.edit');
        Route::put('/admin/retribusi/operasional-item/{operasional_item}', [OperasionalController::class, 'updateItem'])
            ->name('operasional-item.update');
        Route::delete('/admin/retribusi/operasional-item/{operasional_item}', [OperasionalController::class, 'destroyItem'])
            ->name('operasional-item.destroy');
    });

    Route::middleware('permission:retribusi.laporan_mingguan')->group(function () {
        Route::get('/admin/retribusi/laporan_mingguan/print', [LaporanMingguanController::class, 'print'])
            ->name('laporan-mingguan.print');
        Route::resource('/admin/retribusi/laporan_mingguan', LaporanMingguanController::class)
            ->parameters(['laporan_mingguan' => 'laporan_mingguan'])
            ->names('laporan-mingguan');
    });

    Route::middleware('permission:retribusi.laporan_harian')->group(function () {
        Route::get('/admin/retribusi/laporan_harian/print', [LaporanHarianController::class, 'print'])
            ->name('laporan-harian.print');
        Route::get('/admin/retribusi/laporan_harian', [LaporanHarianController::class, 'index'])
            ->name('laporan-harian.index');
    });

    Route::middleware('permission:retribusi.petugas_karcis')->group(function () {
        Route::resource('/admin/retribusi/petugas_karcis', PetugasKarcisController::class)
            ->parameters(['petugas_karcis' => 'petugas_karci'])
            ->names('petugas-karcis');
    });

    Route::middleware('permission:kepegawaian.pegawai')->group(function () {
        Route::resource('/admin/kepegawaian/pegawai', PegawaiController::class)
            ->parameters(['pegawai' => 'pegawai'])
            ->names('pegawai');
        Route::get('/admin/kepegawaian/pegawai/jabatan/create', [JabatanPegawaiController::class, 'create'])
            ->name('jabatan-pegawai.create');
        Route::post('/admin/kepegawaian/pegawai/jabatan', [JabatanPegawaiController::class, 'store'])
            ->name('jabatan-pegawai.store');
        Route::get('/admin/kepegawaian/pegawai/jabatan/{jabatan_pegawai}/edit', [JabatanPegawaiController::class, 'edit'])
            ->name('jabatan-pegawai.edit');
        Route::put('/admin/kepegawaian/pegawai/jabatan/{jabatan_pegawai}', [JabatanPegawaiController::class, 'update'])
            ->name('jabatan-pegawai.update');
        Route::resource('/admin/kepegawaian/pendidikan', PendidikanController::class)
            ->parameters(['pendidikan' => 'pendidikan_pegawai'])
            ->names('pendidikan');
        Route::get('/admin/kepegawaian/dokumen/create', [DokumenPegawaiController::class, 'create'])
            ->name('dokumen-pegawai.create');
        Route::post('/admin/kepegawaian/dokumen', [DokumenPegawaiController::class, 'store'])
            ->name('dokumen-pegawai.store');
        Route::get('/admin/kepegawaian/dokumen/{dokumen_pegawai}/edit', [DokumenPegawaiController::class, 'edit'])
            ->name('dokumen-pegawai.edit');
        Route::put('/admin/kepegawaian/dokumen/{dokumen_pegawai}', [DokumenPegawaiController::class, 'update'])
            ->name('dokumen-pegawai.update');
        Route::delete('/admin/kepegawaian/dokumen/{dokumen_pegawai}', [DokumenPegawaiController::class, 'destroy'])
            ->name('dokumen-pegawai.destroy');
    });

    Route::middleware('permission:kepegawaian.persuratan.approve_kabid')->group(function () {
        Route::get('/admin/kepegawaian/persuratan/surat_keluar/approve_kabid', [PersuratanController::class, 'approveKabid'])
            ->name('persuratan-surat-keluar.approve-kabid');
        Route::post('/admin/kepegawaian/persuratan/surat_keluar/{persuratan}/kabid-approve', [PersuratanController::class, 'kabidApprove'])
            ->name('persuratan-surat-keluar.kabid-approve');
        Route::post('/admin/kepegawaian/persuratan/surat_keluar/{persuratan}/kabid-revisi', [PersuratanController::class, 'kabidRevise'])
            ->name('persuratan-surat-keluar.kabid-revisi');
    });

    Route::middleware('permission:kepegawaian.persuratan.approve_sekretariat')->group(function () {
        Route::get('/admin/kepegawaian/persuratan/surat_keluar/approve_sekretariat', [PersuratanController::class, 'approveSekretariat'])
            ->name('persuratan-surat-keluar.approve-sekretariat');
        Route::post('/admin/kepegawaian/persuratan/surat_keluar/{persuratan}/sekretariat-forward', [PersuratanController::class, 'sekretariatForward'])
            ->name('persuratan-surat-keluar.sekretariat-forward');
        Route::post('/admin/kepegawaian/persuratan/surat_keluar/{persuratan}/sekretariat-number-send', [PersuratanController::class, 'sekretariatNumberAndSend'])
            ->name('persuratan-surat-keluar.sekretariat-number-send');
        Route::post('/admin/kepegawaian/persuratan/surat_keluar/{persuratan}/sekretariat-revisi', [PersuratanController::class, 'sekretariatRevise'])
            ->name('persuratan-surat-keluar.sekretariat-revisi');
        Route::get('/admin/kepegawaian/persuratan/surat_keluar/{persuratan}/suggest-nomor', [PersuratanController::class, 'suggestNomor'])
            ->name('persuratan-surat-keluar.suggest-nomor');
    });

    Route::middleware('permission:kepegawaian.persuratan.approve_kadis')->group(function () {
        Route::get('/admin/kepegawaian/persuratan/surat_keluar/approve_kadis', [PersuratanController::class, 'approveKadis'])
            ->name('persuratan-surat-keluar.approve-kadis');
        Route::post('/admin/kepegawaian/persuratan/surat_keluar/{persuratan}/kadis-sign', [PersuratanController::class, 'kadisSign'])
            ->name('persuratan-surat-keluar.kadis-sign');
        Route::post('/admin/kepegawaian/persuratan/surat_keluar/{persuratan}/kadis-revisi', [PersuratanController::class, 'kadisRevise'])
            ->name('persuratan-surat-keluar.kadis-revisi');
    });

    Route::middleware('permission:kepegawaian.persuratan.surat_keluar')->group(function () {
        Route::get('/admin/kepegawaian/persuratan/surat_keluar/{persuratan}/print', [PersuratanController::class, 'print'])
            ->name('persuratan-surat-keluar.print');
        Route::get('/admin/kepegawaian/persuratan/surat_keluar/{persuratan}/paket', [PersuratanController::class, 'downloadPaket'])
            ->name('persuratan-surat-keluar.download-paket');
        Route::post('/admin/kepegawaian/persuratan/surat_keluar/{persuratan}/submit', [PersuratanController::class, 'submit'])
            ->name('persuratan-surat-keluar.submit');
        Route::post('/admin/kepegawaian/persuratan/surat_keluar/{persuratan}/cancel', [PersuratanController::class, 'cancel'])
            ->name('persuratan-surat-keluar.cancel');
        Route::post('/admin/kepegawaian/persuratan/surat_keluar/{persuratan}/archive', [PersuratanController::class, 'archive'])
            ->name('persuratan-surat-keluar.archive');
        Route::resource('/admin/kepegawaian/persuratan/surat_keluar', PersuratanController::class)
            ->parameters(['surat_keluar' => 'persuratan'])
            ->names('persuratan-surat-keluar');

        Route::name('persuratan-disposisi.')
            ->prefix('/admin/kepegawaian/persuratan/disposisi')
            ->group(function () {
                Route::view('/', 'admin.Kepegawaian.persuratan.disposisi.index')->name('index');
            });
    });

    Route::middleware('permission:kepegawaian.persuratan.arsip_surat_keluar,kepegawaian.persuratan.surat_keluar,kepegawaian.persuratan.approve_sekretariat,kepegawaian.persuratan.approve_kadis')->group(function () {
        Route::name('persuratan-arsip.')
            ->prefix('/admin/kepegawaian/persuratan/arsip')
            ->group(function () {
                Route::get('/', [ArsipSuratKeluarController::class, 'index'])->name('index');
            });
    });

    Route::name('persuratan-masuk.')
        ->prefix('/admin/persuratan_masuk')
        ->group(function () {
            Route::middleware('permission:kepegawaian.persuratan.surat_masuk')->group(function () {
                Route::get('/', [SuratMasukController::class, 'index'])->name('index');
                Route::get('/export', [SuratMasukController::class, 'export'])->name('export');
                Route::get('/create', [SuratMasukController::class, 'create'])->name('create');
                Route::post('/', [SuratMasukController::class, 'store'])->name('store');
                Route::get('/proses-sekretariat', [SuratMasukController::class, 'prosesSekretariat'])->name('proses-sekretariat');
                Route::get('/arsip', [SuratMasukController::class, 'arsip'])->name('arsip');
                Route::post('/{surat_masuk}/agenda', [SuratMasukController::class, 'setAgenda'])->name('agenda');
                Route::post('/{surat_masuk}/forward-kadis', [SuratMasukController::class, 'forwardToKadis'])->name('forward-kadis');
                Route::post('/{surat_masuk}/archive', [SuratMasukController::class, 'archive'])->name('archive');
                Route::post('/{surat_masuk}/cancel', [SuratMasukController::class, 'cancel'])->name('cancel');
                Route::get('/{surat_masuk}/edit', [SuratMasukController::class, 'edit'])->name('edit');
                Route::put('/{surat_masuk}', [SuratMasukController::class, 'update'])->name('update');
                Route::delete('/{surat_masuk}', [SuratMasukController::class, 'destroy'])->name('destroy');
            });

            Route::middleware('permission:kepegawaian.persuratan_masuk.approve_kadis,kepegawaian.persuratan.approve_kadis')->group(function () {
                Route::get('/disposisi-kadis', [SuratMasukController::class, 'disposisiKadis'])->name('disposisi-kadis');
                Route::post('/{surat_masuk}/kadis-dispose', [SuratMasukController::class, 'kadisDispose'])->name('kadis-dispose');
            });

            Route::middleware('permission:kepegawaian.persuratan_masuk.approve_kabid,kepegawaian.persuratan.approve_kabid')->group(function () {
                Route::get('/tindak-lanjut-unit', [SuratMasukController::class, 'tindakLanjutUnit'])->name('tindak-lanjut-unit');
                Route::post('/{surat_masuk}/process', [SuratMasukController::class, 'startProcess'])->name('process');
                Route::post('/{surat_masuk}/disposisi/{disposisi}/complete', [SuratMasukController::class, 'completeDisposisi'])->name('complete-disposisi');
            });

            Route::middleware('permission:kepegawaian.persuratan.surat_masuk,kepegawaian.persuratan_masuk.approve_kabid,kepegawaian.persuratan_masuk.approve_kadis,kepegawaian.persuratan.approve_kabid,kepegawaian.persuratan.approve_kadis')->group(function () {
                Route::get('/{surat_masuk}', [SuratMasukController::class, 'show'])->name('show');
            });
        });

    Route::middleware('permission:kepegawaian.persuratan.manajemen_ttd')->group(function () {
        Route::resource('/admin/kepegawaian/persuratan/manajemen_ttd', ManajemenTtdController::class)
            ->parameters(['manajemen_ttd' => 'manajemen_ttd'])
            ->names('manajemen-ttd');
    });
});
