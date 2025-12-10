<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'                  => ['required', 'string', 'max:100'],
            'username'              => ['required', 'alpha_dash', 'max:60', 'unique:users,username'],
            'password'              => ['required', 'string', 'min:8'],
            // expects password_confirmation
            'picture'               => ['nullable', 'image'],
        ];
    }
}
