<?php

namespace App\Services;

use App\Models\SuratKeluar;
use Illuminate\Support\Str;

class SuratKeluarNomorService
{
    public function suggestNext(?int $year = null): string
    {
        $year = $year ?? (int) date('Y');
        $prefix = 'DISHUB';

        $count = SuratKeluar::query()
            ->whereYear('tanggal_surat', $year)
            ->whereNotNull('nomor_surat')
            ->where('nomor_surat', 'like', '%/'.$prefix.'/'.$year)
            ->count();

        $seq = str_pad((string) ($count + 1), 3, '0', STR_PAD_LEFT);

        return $seq.'/'.$prefix.'/'.$year;
    }

    public function generateVerificationCode(): string
    {
        do {
            $code = strtoupper(Str::random(10));
        } while (SuratKeluar::query()->where('verification_code', $code)->exists());

        return $code;
    }
}
