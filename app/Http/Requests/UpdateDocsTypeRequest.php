<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDocsTypeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route('docs_type')?->id ?? $this->route('docs_type');

        return [
            'name' => ['required', 'string', 'max:255', "unique:docs_types,name,{$id}"],
        ];
    }
}
