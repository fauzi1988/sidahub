<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateUserAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user || (! $user->is_super_admin && ! $user->hasPermission('pengaturan.pengguna'))) {
            return false;
        }

        $target = $this->route('pengguna');
        if ($target instanceof User && $target->is_super_admin && ! $user->is_super_admin) {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        /** @var User $target */
        $target = $this->route('pengguna');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($target->id),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
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
        });
    }
}
