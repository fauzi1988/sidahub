<?php

namespace App\Http\Requests;

use App\Models\SuratMasuk;
use App\Models\SuratMasukDisposisi;
use Illuminate\Foundation\Http\FormRequest;

class SuratMasukCompleteDisposisiRequest extends FormRequest
{
    public function authorize(): bool
    {
        $surat = $this->route('surat_masuk');
        $disposisi = $this->resolveDisposisi();

        return $surat instanceof SuratMasuk
            && $disposisi instanceof SuratMasukDisposisi
            && ($this->user()?->can('completeDisposisi', [$surat, $disposisi]) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function disposisi(): SuratMasukDisposisi
    {
        $disposisi = $this->resolveDisposisi();
        if (! $disposisi instanceof SuratMasukDisposisi) {
            abort(404);
        }

        return $disposisi;
    }

    private function resolveDisposisi(): ?SuratMasukDisposisi
    {
        $id = $this->route('disposisi') ?? $this->input('disposisi_id');

        if (! $id) {
            return null;
        }

        return SuratMasukDisposisi::query()->find($id);
    }
}
