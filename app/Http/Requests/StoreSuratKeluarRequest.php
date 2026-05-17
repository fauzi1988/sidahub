<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\SanitizesSuratKeluarIsi;
use App\Models\SuratKeluar;
use App\Rules\MinPlainTextLength;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSuratKeluarRequest extends FormRequest
{
    use SanitizesSuratKeluarIsi;
    public function authorize(): bool
    {
        return $this->user()?->can('create', SuratKeluar::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'tanggal_surat' => ['required', 'date'],
            'perihal' => ['required', 'string', 'max:255'],
            'tujuan_surat' => ['required', 'string', 'max:255'],
            'alamat_tujuan' => ['nullable', 'string'],
            'jenis_surat' => ['required', Rule::in(array_keys(SuratKeluar::jenisSuratOptions()))],
            'sifat_surat' => ['required', Rule::in(array_keys(SuratKeluar::sifatSuratOptions()))],
            'prioritas' => ['required', Rule::in(array_keys(SuratKeluar::prioritasOptions()))],
            'id_pegawai_pengusul' => ['nullable', 'exists:pegawai,id_pegawai'],
            'id_pegawai_penandatangan' => ['nullable', 'exists:pegawai,id_pegawai'],
            'ringkasan' => ['nullable', 'string'],
            'isi_surat' => ['required', 'string', new MinPlainTextLength(20)],
            'catatan' => ['nullable', 'string'],
            'lampiran' => ['nullable', 'array', 'max:5'],
            'lampiran.*' => ['file', 'max:5120', 'mimes:pdf,doc,docx,jpg,jpeg,png'],
        ];
    }
}
