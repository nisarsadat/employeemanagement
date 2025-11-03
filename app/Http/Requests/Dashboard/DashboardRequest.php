<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class DashboardRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'range'  => 'nullable|in:today,last7d,last30d,thisMonth,thisYear',
            'zone'   => 'nullable|string|max:100',
            'hawza'  => 'nullable|string|max:100',
            'status' => 'nullable|in:فعال,غیر فعال',
            'batch'  => 'nullable|integer|min:1|max:5',
        ];
    }
}
