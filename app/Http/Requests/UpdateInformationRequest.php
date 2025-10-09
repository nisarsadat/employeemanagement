<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInformationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        // same as store, but all fields optional
        return (new StoreInformationRequest())->rules();
    }
}
