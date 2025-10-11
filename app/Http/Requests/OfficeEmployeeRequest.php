<?php
// app/Http/Requests/OfficeEmployeeRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\UniqueCodeNumberAcrossStaff;

class OfficeEmployeeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route('office_employee')?->id ?? $this->route('id');

        $base = [
            'picture'        => ['nullable','image','max:2048'],
            'name'           => ['required','string'],
            'father_name'    => ['nullable','string'],
            'nickname'       => ['nullable','string'],
            'main_province'  => ['nullable','string'],
            'main_district'  => ['nullable','string'],
            'main_village'   => ['nullable','string'],
            'now_province'   => ['nullable','string'],
            'now_district'   => ['nullable','string'],
            'now_village'    => ['nullable','string'],
            'releg_study'    => ['nullable','string'],
            'new_study'      => ['nullable','string'],
            'phone_number'   => ['nullable','string','max:50'],
            'tazkira'        => ['nullable','string','max:100'],
            'apply_date'     => ['nullable','string'],
            'join_date'      => ['nullable','string'],
            'left_date'      => ['nullable','string'],
            'notes'          => ['nullable','string'],
            'old_picture'    => ['nullable','string'],
            // ⬇️ NEW
            'code_number'    => [
                $this->isMethod('POST') ? 'required' : 'sometimes',
                'string','max:100',
                new UniqueCodeNumberAcrossStaff('office_employees', $id),
            ],
        ];

        // For PATCH-like updates you can wrap everything with `sometimes`
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            foreach ($base as $key => $rules) {
                $base[$key] = is_array($rules) ? array_merge(['sometimes'], $rules) : ['sometimes', $rules];
            }
            // keep name required on PUT if you want:
            // $base['name'][0] = 'required';
        }

        return $base;
    }
}
