<?php

namespace App\Rules;

use App\Services\SuratKeluarIsiSanitizer;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MinPlainTextLength implements ValidationRule
{
    public function __construct(
        private readonly int $min = 20,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $length = app(SuratKeluarIsiSanitizer::class)->plainTextLength(is_string($value) ? $value : '');

        if ($length < $this->min) {
            $fail("Isi surat minimal {$this->min} karakter teks.");
        }
    }
}
