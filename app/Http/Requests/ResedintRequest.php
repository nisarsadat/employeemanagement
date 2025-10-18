<?php
// app/Http/Requests/ResedintRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\UniqueCodeNumberAcrossStaff;

class ResedintRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route('resedint')?->id ?? $this->route('id');

        $base = [
            'picture'           => ['nullable','image'],
            'name'              => ['required','string'],
            'father_name'       => ['nullable','string'],
            'nickname'          => ['nullable','string'],
            'main_province'     => ['nullable','string'],
            'main_district'     => ['nullable','string'],
            'main_village'      => ['nullable','string'],
            'now_province'      => ['nullable','string'],
            'now_district'      => ['nullable','string'],
            'now_village'       => ['nullable','string'],
            'zone'              => ['nullable','string'],
            'hawza'             => ['nullable','string'],
            'phone_number'      => ['nullable','string'],
            'whatsapp_number'   => ['nullable','string'],
            'officeemployee_id' => ['nullable','integer','exists:office_employees,id'],
            'notes'             => ['nullable','string'],
            'old_picture'       => ['nullable','string'],

            // ⬇️ NEW
            'code_number'       => [
                $this->isMethod('POST') ? 'required' : 'sometimes',
                'string','max:100',
                new UniqueCodeNumberAcrossStaff('resedints', $id),
            ],
            'coordinates'       => ['nullable','string'], 

        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            foreach ($base as $k => $rules) {
                $base[$k] = is_array($rules) ? array_merge(['sometimes'], $rules) : ['sometimes', $rules];
            }
        }

        return $base;
    }
}
