<?php

namespace Tests\Unit;

use App\Models\SuratKeluar;
use App\Services\SuratKeluarPrintPdfService;
use Carbon\Carbon;
use Tests\TestCase;

class SuratKeluarPrintPdfServiceTest extends TestCase
{
    public function test_generate_returns_pdf_without_lampiran(): void
    {
        $surat = new SuratKeluar([
            'id_surat_keluar' => 1,
            'tanggal_surat' => Carbon::parse('2026-05-17'),
            'tanggal_kirim' => Carbon::parse('2026-05-17'),
            'perihal' => 'Uji cetak PDF',
            'tujuan_surat' => 'Instansi X',
            'alamat_tujuan' => 'di Tempat',
            'jenis_surat' => 'surat_dinas',
            'sifat_surat' => 'biasa',
            'prioritas' => 'normal',
            'status' => 'dikirim',
            'nomor_surat' => '001/TEST/2026',
            'isi_surat' => '<p>Isi surat uji cetak PDF tanpa lampiran.</p>',
            'lampiran' => null,
        ]);

        $pdf = app(SuratKeluarPrintPdfService::class)->generate($surat, null);

        $this->assertStringStartsWith('%PDF', $pdf);
        $this->assertGreaterThan(500, strlen($pdf));
    }
}
