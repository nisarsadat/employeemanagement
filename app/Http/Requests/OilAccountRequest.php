<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OilAccountRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array {
        $id = $this->route('oil_account')?->id ?? null;

        return [
            'name'     => ['required','string','max:255','unique:oil_accounts,name'.($id?",$id":'')],
            'details'  => ['nullable','string'],
            // quantity maintained by logic; allow manual set only if you want:
            'quantity' => ['nullable','numeric','min:0'],
        ];
    }
}
