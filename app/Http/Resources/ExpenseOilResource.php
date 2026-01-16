<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseOilResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'oil_account_id' => $this->oil_account_id,
            'employee_id'    => $this->employee_id,

            'name'           => $this->name,
            'father_name'    => $this->father_name,
            'date'           => optional($this->date)->format('Y-m-d'),

            'liter_in_number'=> $this->liter_in_number,
            'liter_in_words' => $this->liter_in_words,

            'account_name'   => $this->account_name,
            'tafseel'        => $this->tafseel,
            'payer'          => $this->payer,
            'note'           => $this->note,

            // flattened helpers (optional)
            'account_name_from_relation' => optional($this->account)->name,

            'account' => $this->whenLoaded('account'),
            'employee'=> $this->whenLoaded('employee'),
        ];
    }
}
