<?php

namespace App\Services;

use Mews\Purifier\Facades\Purifier;

class SuratKeluarIsiSanitizer
{
    public function sanitize(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        return trim(Purifier::clean($html, 'surat_isi'));
    }

    public function plainTextLength(?string $html): int
    {
        $plain = html_entity_decode(strip_tags($this->sanitize($html)), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return mb_strlen(trim(preg_replace('/\s+/u', ' ', $plain) ?? ''));
    }

    public function forDisplay(?string $html): string
    {
        $clean = $this->sanitize($html);

        if ($clean === '') {
            return '-';
        }

        if (! preg_match('/<(p|table|ul|ol|h[1-6]|div|br)\b/i', $clean)) {
            return nl2br(e($clean), false);
        }

        return $clean;
    }
}
