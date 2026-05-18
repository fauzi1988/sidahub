<?php

namespace App\Services;

use App\Models\SuratKeluar;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;
use Throwable;

class SuratKeluarPrintPdfService
{
    public function generate(SuratKeluar $persuratan, ?string $verifyUrl): string
    {
        $mainPdf = $this->renderMainPdf($persuratan, $verifyUrl);
        $lampiran = $persuratan->lampiran ?? [];

        if ($lampiran === []) {
            return $mainPdf;
        }

        try {
            return $this->mergeWithLampiran($mainPdf, $lampiran);
        } catch (Throwable $e) {
            Log::error('Surat keluar: gagal menggabungkan lampiran ke PDF', [
                'surat_id' => $persuratan->id_surat_keluar,
                'error' => $e->getMessage(),
            ]);

            return $mainPdf;
        }
    }

    private function renderMainPdf(SuratKeluar $persuratan, ?string $verifyUrl): string
    {
        return Pdf::loadView('admin.Kepegawaian.persuratan.surat_keluar.print', [
            'persuratan' => $persuratan,
            'verifyUrl' => $verifyUrl,
        ])->setPaper('A4', 'portrait')->output();
    }

    /**
     * @param  list<array{path?: string, name?: string, size?: int}>  $lampiran
     */
    private function mergeWithLampiran(string $mainPdf, array $lampiran): string
    {
        $merger = new Fpdi;
        $merger->SetAutoPageBreak(false);

        $this->appendPdfBytes($merger, $mainPdf);

        $lampiranIndex = 0;
        foreach ($lampiran as $file) {
            $path = $file['path'] ?? null;
            if (! $path || ! Storage::disk('public')->exists($path)) {
                continue;
            }

            $lampiranIndex++;
            $fullPath = Storage::disk('public')->path($path);
            $name = $file['name'] ?? basename($path);
            $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

            $this->appendHtmlPdf($merger, 'admin.Kepegawaian.persuratan.surat_keluar.print-lampiran-separator', [
                'number' => $lampiranIndex,
                'name' => $name,
            ]);

            match ($ext) {
                'pdf' => $this->appendPdfFile($merger, $fullPath),
                'jpg', 'jpeg', 'png' => $this->appendImageFile($merger, $fullPath, $ext),
                default => $this->appendHtmlPdf($merger, 'admin.Kepegawaian.persuratan.surat_keluar.print-lampiran-placeholder', [
                    'name' => $name,
                    'ext' => $ext,
                ]),
            };
        }

        return $merger->Output('S');
    }

    private function appendPdfBytes(Fpdi $merger, string $pdfBytes): void
    {
        $temp = $this->writeTempFile($pdfBytes);
        try {
            $this->appendPdfFile($merger, $temp);
        } finally {
            @unlink($temp);
        }
    }

    private function appendPdfFile(Fpdi $merger, string $filePath): void
    {
        try {
            $pageCount = $merger->setSourceFile($filePath);
        } catch (Throwable $e) {
            Log::warning('Surat keluar: lampiran PDF tidak dapat dibaca', [
                'path' => $filePath,
                'error' => $e->getMessage(),
            ]);

            return;
        }

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $merger->importPage($pageNo);
            $size = $merger->getTemplateSize($templateId);
            $orientation = ($size['width'] ?? 0) > ($size['height'] ?? 0) ? 'L' : 'P';
            $merger->AddPage($orientation, [$size['width'], $size['height']]);
            $merger->useTemplate($templateId);
        }
    }

    private function appendImageFile(Fpdi $merger, string $imagePath, string $ext): void
    {
        if (! is_readable($imagePath)) {
            return;
        }

        $merger->AddPage('P', 'A4');
        $type = match ($ext) {
            'png' => 'PNG',
            'jpg', 'jpeg' => 'JPEG',
            default => '',
        };

        $merger->Image($imagePath, 10, 15, 190, 0, $type);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function appendHtmlPdf(Fpdi $merger, string $view, array $data): void
    {
        $html = view($view, $data)->render();
        $pdf = Pdf::loadHTML($html)->setPaper('A4', 'portrait')->output();
        $this->appendPdfBytes($merger, $pdf);
    }

    private function writeTempFile(string $contents): string
    {
        $temp = tempnam(sys_get_temp_dir(), 'sk-pdf-');
        if ($temp === false) {
            throw new \RuntimeException('Tidak dapat membuat file sementara untuk PDF.');
        }

        file_put_contents($temp, $contents);

        return $temp;
    }
}
