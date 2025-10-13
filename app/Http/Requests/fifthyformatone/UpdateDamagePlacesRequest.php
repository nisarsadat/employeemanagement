<?php

namespace App\Http\Requests\fifthyformatone;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDamagePlacesRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return (new StoreDamageRasanPlacesRequest())->rules();
    }
}
