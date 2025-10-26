<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    public function toArray($request)
    {
        // Map payee display fields
        $payee = null;
        if ($this->relationLoaded('payee') && $this->payee) {
            $payee = [
                'type' => class_basename($this->payee_type),
                'id'   => $this->payee->id,
                // Use name/code for Resource and OfficeEmployee
                'name' => $this->payee->name ?? ($this->payee->full_name ?? null),
                'code' => $this->payee->code ?? ($this->payee->code_number ?? null),
            ];
        }

        return [
            'id'            => $this->id,
            'amount_price'  => $this->amount_price,
            'paid_at'       => $this->paid_at?->toDateString(),
            'phone_number'  => $this->phone_number,
            'reason_expense'=> $this->reason_expense,
            'note'          => $this->note,
            'date'        => $this->received_at,
            'account'       => $this->whenLoaded('account', fn() => [
                'id' => $this->account->id,
                'name' => $this->account->name,
                'price' => $this->account->price,
            ]),
            'payee'         => $payee,
        ];
    }
}

