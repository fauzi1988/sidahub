<?php

namespace App\Services;

use App\Models\SuratKeluar;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

class SuratKeluarPaketService
{
    public function __construct(
        private readonly SuratKeluarPrintPdfService $printPdfService,
    ) {}

    /**
     * Membuat file ZIP sementara berisi surat.pdf dan folder lampiran/.
     */
    public function createPackageZip(SuratKeluar $persuratan, ?string $verifyUrl): string
    {
        $zipPath = tempnam(sys_get_temp_dir(), 'sk-paket-');
        if ($zipPath === false) {
            throw new RuntimeException('Tidak dapat membuat file ZIP sementara.');
        }

        $zipFile = $zipPath.'.zip';
        @unlink($zipPath);

        $zip = new ZipArchive;
        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Tidak dapat membuka arsip ZIP.');
        }

        $pdfBinary = $this->printPdfService->generate($persuratan, $verifyUrl);
        $zip->addFromString('surat.pdf', $pdfBinary);
        $zip->addFromString('PETUNJUK-CETAK.txt', $this->petunjukCetak($persuratan));

        $lampiran = $persuratan->lampiran ?? [];
        $usedNames = [];

        foreach ($lampiran as $index => $file) {
            $path = $file['path'] ?? null;
            if (! $path || ! Storage::disk('public')->exists($path)) {
                continue;
            }

            $originalName = $file['name'] ?? basename($path);
            $entryName = $this->uniqueLampiranEntryName($originalName, $index + 1, $usedNames);
            $zip->addFile(Storage::disk('public')->path($path), 'lampiran/'.$entryName);
        }

        $zip->close();

        return $zipFile;
    }

    public function packageFilename(SuratKeluar $persuratan): string
    {
        $slug = $persuratan->nomor_surat
            ? preg_replace('/[^A-Za-z0-9\-]+/', '-', $persuratan->nomor_surat)
            : (string) $persuratan->id_surat_keluar;

        return 'paket-surat-keluar-'.$slug.'.zip';
    }

    private function petunjukCetak(SuratKeluar $persuratan): string
    {
        $jumlah = count($persuratan->lampiran ?? []);

        $lines = [
            'PAKET SURAT KELUAR — PETUNJUK CETAK',
            '====================================',
            '',
            '1. surat.pdf',
            '   Cetak file ini untuk surat utama.',
            '   Lampiran berformat PDF dan gambar sudah digabung di dalam file ini.',
            '',
        ];

        if ($jumlah > 0) {
            $lines[] = '2. Folder lampiran/ ('.$jumlah.' file)';
            $lines[] = '   Berisi file lampiran asli yang diunggah.';
            $lines[] = '   Untuk file Word (DOC/DOCX), buka file di folder ini lalu cetak manual.';
            $lines[] = '   File PDF/gambar di folder ini sama dengan yang sudah digabung di surat.pdf,';
            $lines[] = '   disediakan sebagai salinan asli jika diperlukan.';
        } else {
            $lines[] = '2. Tidak ada lampiran terpisah pada surat ini.';
        }

        $lines[] = '';
        $lines[] = 'Dibuat dari Sistem Informasi Dishub Halmahera Timur.';

        return implode(PHP_EOL, $lines);
    }

    /**
     * @param  list<string>  $usedNames
     */
    private function uniqueLampiranEntryName(string $originalName, int $order, array &$usedNames): string
    {
        $base = pathinfo($originalName, PATHINFO_FILENAME);
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $safeBase = Str::slug($base) ?: 'lampiran';
        $suffix = $ext !== '' ? '.'.$ext : '';
        $candidate = sprintf('%02d-%s%s', $order, $safeBase, $suffix);

        if (! in_array($candidate, $usedNames, true)) {
            $usedNames[] = $candidate;

            return $candidate;
        }

        $counter = 2;
        do {
            $candidate = sprintf('%02d-%s-%d%s', $order, $safeBase, $counter, $suffix);
            $counter++;
        } while (in_array($candidate, $usedNames, true));

        $usedNames[] = $candidate;

        return $candidate;
    }
}
