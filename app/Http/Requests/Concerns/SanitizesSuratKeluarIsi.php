<?php

namespace App\Http\Requests\Concerns;

use App\Services\SuratKeluarIsiSanitizer;

trait SanitizesSuratKeluarIsi
{
    protected function prepareForValidation(): void
    {
        if ($this->has('isi_surat')) {
            $this->merge([
                'isi_surat' => app(SuratKeluarIsiSanitizer::class)->sanitize(
                    $this->input('isi_surat')
                ),
            ]);
        }
    }
}
