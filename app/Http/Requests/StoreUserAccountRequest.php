<?php

namespace App\Http\Requests;

use App\Models\Pegawai;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreUserAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) ($user && ($user->is_super_admin || $user->hasPermission('pengaturan.pengguna')));
    }

    public function rules(): array
    {
        return [
            'id_pegawai' => [
                'required',
                'integer',
                Rule::exists('pegawai', 'id_pegawai'),
                Rule::unique('users', 'id_pegawai'),
            ],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'is_super_admin' => ['boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'max:120'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $auth = $this->user();
            if ($this->boolean('is_super_admin') && $auth && ! $auth->is_super_admin) {
                $v->errors()->add('is_super_admin', 'Hanya super admin yang dapat menetapkan status super admin.');
            }

            $idPegawai = (int) $this->input('id_pegawai', 0);
            if ($idPegawai > 0) {
                $pegawai = Pegawai::query()->with('user')->find($idPegawai);
                if (! $pegawai) {
                    $v->errors()->add('id_pegawai', 'Pegawai tidak ditemukan.');

                    return;
                }
                if ($pegawai->user) {
                    $v->errors()->add('id_pegawai', 'Pegawai ini sudah memiliki akun aplikasi.');
                }
            }
        });
    }
}
