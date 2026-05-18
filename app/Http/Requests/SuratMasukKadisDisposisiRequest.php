<?php

namespace App\Http\Requests;

use App\Models\SuratMasuk;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SuratMasukKadisDisposisiRequest extends FormRequest
{
    public function authorize(): bool
    {
        $surat = $this->route('surat_masuk');

        return $surat instanceof SuratMasuk && ($this->user()?->can('kadisDispose', $surat) ?? false);
    }

    protected function prepareForValidation(): void
    {
        $rows = $this->input('disposisi', []);
        if (! is_array($rows)) {
            return;
        }

        $filtered = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $instruksi = trim((string) ($row['instruksi'] ?? ''));
            if ($instruksi === '') {
                continue;
            }

            $filtered[] = [
                'to_pegawai_id' => ! empty($row['to_pegawai_id']) ? $row['to_pegawai_id'] : null,
                'to_unit_kerja' => ! empty($row['to_unit_kerja']) ? trim((string) $row['to_unit_kerja']) : null,
                'instruksi' => $instruksi,
                'batas_waktu' => ! empty($row['batas_waktu']) ? $row['batas_waktu'] : null,
            ];
        }

        $this->merge(['disposisi' => array_values($filtered)]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            foreach ($this->input('disposisi', []) as $index => $row) {
                $hasPegawai = ! empty($row['to_pegawai_id']);
                $hasUnit = ! empty($row['to_unit_kerja']);
                if (! $hasPegawai && ! $hasUnit) {
                    $validator->errors()->add(
                        "disposisi.{$index}.to_pegawai_id",
                        'Pilih pegawai atau unit kerja tujuan disposisi.',
                    );
                }
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'note' => ['nullable', 'string', 'max:2000'],
            'disposisi' => ['required', 'array', 'min:1', 'max:10'],
            'disposisi.*.to_pegawai_id' => ['nullable', 'exists:pegawai,id_pegawai'],
            'disposisi.*.to_unit_kerja' => ['nullable', 'string', 'max:150'],
            'disposisi.*.instruksi' => ['required', 'string', 'max:2000'],
            'disposisi.*.batas_waktu' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'disposisi.required' => 'Minimal satu disposisi dengan instruksi wajib diisi.',
            'disposisi.min' => 'Minimal satu disposisi dengan instruksi wajib diisi.',
            'disposisi.*.instruksi.required' => 'Instruksi disposisi wajib diisi.',
        ];
    }
}
