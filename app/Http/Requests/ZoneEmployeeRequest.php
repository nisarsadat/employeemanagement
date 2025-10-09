<?php
// app/Http/Requests/ZoneEmployeeRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\UniqueCodeNumberAcrossStaff;

class ZoneEmployeeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route('zone_employee')?->id ?? $this->route('id');

        $base = [
            'picture'         => ['nullable','image','max:2048'],
            'name'            => ['required','string','max:255'],
            'father_name'     => ['nullable','string','max:255'],
            'nickname'        => ['nullable','string','max:255'],
            'code_number'     => [
                $this->isMethod('POST') ? 'required' : 'sometimes',
                'string','max:100',
                new UniqueCodeNumberAcrossStaff('zone_employees', $id),
            ],
            'hawza'           => ['nullable','string','max:255'],
            'zone'            => ['nullable','string','max:255'],
            'degree'          => ['nullable','string','max:255'],
            'zone_entry_hour' => ['nullable','string','max:50'],
            'resedint_id'     => ['nullable','integer','exists:resedints,id'],
            'notes'           => ['nullable','string'],
            'old_picture'     => ['nullable','string'],
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            foreach ($base as $k => $rules) {
                $base[$k] = is_array($rules) ? array_merge(['sometimes'], $rules) : ['sometimes', $rules];
            }
        }

        return $base;
    }
}
