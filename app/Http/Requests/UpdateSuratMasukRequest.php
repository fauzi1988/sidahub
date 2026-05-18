<?php

namespace App\Http\Requests;

use App\Models\SuratMasuk;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSuratMasukRequest extends FormRequest
{
    public function authorize(): bool
    {
        $surat = $this->route('surat_masuk');

        return $surat instanceof SuratMasuk && ($this->user()?->can('update', $surat) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nomor_surat_pengirim' => ['nullable', 'string', 'max:120'],
            'tanggal_surat' => ['required', 'date'],
            'tanggal_terima' => ['required', 'date'],
            'perihal' => ['required', 'string', 'max:255'],
            'pengirim' => ['required', 'string', 'max:255'],
            'sifat_surat' => ['required', Rule::in(array_keys(SuratMasuk::sifatSuratOptions()))],
            'ringkasan' => ['nullable', 'string'],
            'lampiran' => ['nullable', 'array', 'max:5'],
            'lampiran.*' => ['file', 'max:5120', 'mimes:pdf,doc,docx,jpg,jpeg,png'],
            'hapus_lampiran' => ['nullable', 'array'],
            'hapus_lampiran.*' => ['string', 'max:500'],
            'surat_keluar_balasan_id' => ['nullable', 'exists:surat_keluar,id_surat_keluar'],
        ];
    }
}
