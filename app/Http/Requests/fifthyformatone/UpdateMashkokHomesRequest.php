<?php

namespace App\Http\Requests\fifthyformatone;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMashkokHomesRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return (new StoreMashkokHomesRequest())->rules();
    }
}
