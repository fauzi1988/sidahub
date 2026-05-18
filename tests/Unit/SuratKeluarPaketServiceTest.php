<?php

namespace Tests\Unit;

use App\Models\SuratKeluar;
use App\Services\SuratKeluarPaketService;
use Carbon\Carbon;
use Tests\TestCase;
use ZipArchive;

class SuratKeluarPaketServiceTest extends TestCase
{
    public function test_create_package_zip_contains_surat_pdf_and_petunjuk(): void
    {
        $surat = new SuratKeluar([
            'id_surat_keluar' => 1,
            'tanggal_surat' => Carbon::parse('2026-05-17'),
            'tanggal_kirim' => Carbon::parse('2026-05-17'),
            'perihal' => 'Uji paket unduh',
            'tujuan_surat' => 'Instansi X',
            'alamat_tujuan' => 'di Tempat',
            'jenis_surat' => 'surat_dinas',
            'sifat_surat' => 'biasa',
            'prioritas' => 'normal',
            'status' => 'dikirim',
            'nomor_surat' => '001/PAKET/2026',
            'isi_surat' => '<p>Isi surat untuk paket unduh.</p>',
            'lampiran' => null,
        ]);

        $service = app(SuratKeluarPaketService::class);
        $zipPath = $service->createPackageZip($surat, null);

        try {
            $this->assertFileExists($zipPath);

            $zip = new ZipArchive;
            $this->assertTrue($zip->open($zipPath));
            $this->assertNotFalse($zip->locateName('surat.pdf'));
            $this->assertNotFalse($zip->locateName('PETUNJUK-CETAK.txt'));
            $zip->close();
        } finally {
            @unlink($zipPath);
        }
    }
}
