<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $userId = $this->route('user')?->id ?? $this->route('user');

        return [
            'name'        => ['sometimes', 'required', 'string', 'max:100'],
            'username'    => ['sometimes', 'required', 'alpha_dash', 'max:60', Rule::unique('users','username')->ignore($userId)],
            'password'    => ['sometimes', 'required', 'string', 'min:8'],
            'picture'     => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:2048'],
            'active'      => ['sometimes','boolean'],
        ];
    }
}
