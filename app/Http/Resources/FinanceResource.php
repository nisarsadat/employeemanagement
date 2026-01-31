<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FinanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $resource = $this->getRelationValue('resource');
        $employee = $this->getRelationValue('officeEmployee');
        $account  = $this->getRelationValue('account');

        $event = $this->getRelationValue('event');
        $info  = $this->getRelationValue('information');

        return [
            'id' => $this->id,

            'office_employee_id' => $this->office_employee_id,
            'resource_id'        => $this->resource_id,
            'account_id'         => $this->account_id,

            // ✅ NEW: link finance to event/information (nullable)
            'event_id'           => $this->event_id,
            'information_id'     => $this->information_id,

            'money_amount_number' => $this->money_amount_number,
            'money_amount_words'  => $this->money_amount_words,

            'date' => optional($this->date)->toDateString(),
            'employee_phone' => $this->employee_phone,
            'expense_result' => $this->expense_result,

            'money_distributer' => $this->money_distributer,
            'payer' => $this->payer,
            'receiver' => $this->receiver,

            'account_type' => $this->account_type,
            'notes' => $this->notes,

            // Flattened derived fields
            'resource_code' => $resource?->code_number,
            'resource_phone' => $resource?->phone_number,
            'office_employee_code' => $employee?->code_number,
            'office_employee_phone' => $employee?->phone_number,
            'account_name' => $account?->name,

            // Minimal embedded relations
            'resource' => $this->whenLoaded('resource', function () use ($resource) {
                return $resource ? [
                    'id' => $resource->id,
                    'name' => $resource->name,
                    'code_number' => $resource->code_number,
                    'phone_number' => $resource->phone_number,
                    'nick_name' => $resource->nick_name,
                ] : null;
            }),

            'office_employee' => $this->whenLoaded('officeEmployee', function () use ($employee) {
                return $employee ? [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'code_number' => $employee->code_number,
                    'phone_number' => $employee->phone_number,
                ] : null;
            }),

            'account' => $this->whenLoaded('account', function () use ($account) {
                return $account ? [
                    'id' => $account->id,
                    'name' => $account->name,
                    'price' => $account->price,
                ] : null;
            }),

            // ✅ NEW: minimal embedded event/information (safe shape)
            'event' => $this->whenLoaded('event', function () use ($event) {
                return $event ? [
                    'id' => $event->id,
                ] : null;
            }),

            'information' => $this->whenLoaded('information', function () use ($info) {
                return $info ? [
                    'id' => $info->id,
                ] : null;
            }),

            'created_at' => optional($this->created_at)->toDateTimeString(),
            'updated_at' => optional($this->updated_at)->toDateTimeString(),
        ];
    }
}
