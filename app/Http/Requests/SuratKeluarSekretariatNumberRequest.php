<?php

namespace App\Http\Requests;

use App\Models\SuratKeluar;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SuratKeluarSekretariatNumberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('kepegawaian.persuratan.approve_sekretariat')
            || $this->user()?->is_super_admin;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $surat = $this->route('persuratan');

        return [
            'nomor_surat' => [
                'required_unless:use_suggested,1,true',
                'nullable',
                'string',
                'max:120',
                Rule::unique('surat_keluar', 'nomor_surat')->ignore(
                    $surat instanceof SuratKeluar ? $surat->id_surat_keluar : null,
                    'id_surat_keluar'
                ),
            ],
            'tanggal_kirim' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:2000'],
            'use_suggested' => ['nullable', 'boolean'],
        ];
    }
}
