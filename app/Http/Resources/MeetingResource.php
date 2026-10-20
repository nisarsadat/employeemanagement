<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeetingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'meeting_title' => $this->meeting_title,
            'organ' => $this->organ,
            'province' => $this->province,
            'district' => $this->district,
            'specific_place' => $this->specific_place,
            'coordinates' => $this->coordinates,

            'start_date' => $this->start_date
                ? $this->start_date->format('Y-m-d')
                : null,

            'end_date' => $this->end_date
                ? $this->end_date->format('Y-m-d')
                : null,

            'total_time' => $this->total_time,

            'participant' => $this->participant,
            'boss_participant' => $this->boss_participant,

            'office_employee_count' => $this->office_employee_count,

            'leader_name' => $this->leader_name,

            'done_actions' => $this->done_actions,
            'mashkok_situations' => $this->mashkok_situations,
            'problems' => $this->problems,

            'meeting_supplier' => $this->meeting_supplier,
            'meeting_supplier_phone' => $this->meeting_supplier_phone,

            'meeting_amniyat_responsable' =>
                $this->meeting_amniyat_responsable,

            'meeting_amniyat_responsable_phone' =>
                $this->meeting_amniyat_responsable_phone,

            'amniyat_mansobine_count' =>
                $this->amniyat_mansobine_count,

            'note' => $this->note,

            'account_id' => $this->account_id,
            'account' => $this->whenLoaded('account', function () {
                return $this->account ? [
                    'id' => $this->account->id,
                    'name' => $this->account->name,
                    'price' => $this->account->price,
                ] : null;
            }),
            'message_to_finance' => $this->message_to_finance,
            'money_amount_words' => $this->money_amount_words,
            'finance_status' => $this->finance_status ?? 'pending',
            'finance_paid_at' => optional($this->finance_paid_at)?->toISOString(),

            // ✅ ترسره کوونکي
            'office_employee_ids' => $this->whenLoaded(
                'officeEmployees',
                fn () => $this->officeEmployees->pluck('id')->values()
            ),

            'office_employees' => $this->whenLoaded(
                'officeEmployees',
                fn () => $this->officeEmployees->map(fn ($employee) => [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'code_number' => $employee->code_number,
                ])->values()
            ),

            'office_employees_display' => $this->whenLoaded(
                'officeEmployees',
                fn () => $this->office_employees_display
            ),

            'documents' => $this->whenLoaded(
                'documents',
                function () {
                    return $this->documents->map(function ($document) {
                        return [
                            'id' => $document->id,
                            'meeting_id' => $document->meeting_id,
                            'file_name' => $document->file_name,
                            'original_name' => $document->file_name,
                            'file_path' => $document->file_path,
                            'mime_type' => $document->mime_type,
                            'size_bytes' => $document->size_bytes,
                            'url' => $document->file_path
                                ? asset('storage/' . $document->file_path)
                                : null,
                        ];
                    });
                }
            ),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}