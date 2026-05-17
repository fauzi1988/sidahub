<?php

namespace App\Http\Requests;

use App\Models\SuratKeluar;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SuratKeluarKadisSignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('kepegawaian.persuratan.approve_kadis')
            || $this->user()?->is_super_admin;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'jenis_ttd' => ['required', Rule::in(array_keys(SuratKeluar::jenisTtdOptions()))],
            'ttd_management_id' => ['required', 'integer', 'exists:manajemen_ttd,id_ttd'],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
