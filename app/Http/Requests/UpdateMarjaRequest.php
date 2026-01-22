<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMarjaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route('marja')?->id ?? $this->route('marja');

        return [
            'name' => ['required', 'string', 'max:255', "unique:marjas,name,{$id}"],
        ];
    }
}
