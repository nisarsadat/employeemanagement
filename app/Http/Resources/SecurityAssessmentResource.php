<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SecurityAssessmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'place_name' => $this->place_name,
            'responsible_owner_name' => $this->responsible_owner_name,

            'province' => $this->province,
            'district' => $this->district,
            'specific_place' => $this->specific_place,
            'coordinates' => $this->coordinates,
            'north' => $this->north,
            'south' => $this->south,
            'east' => $this->east,
            'west' => $this->west,

            'daily_visitors_count' => $this->daily_visitors_count,

            'risk_places' => $this->risk_places,
            'vulnerable_places' => $this->vulnerable_places,
            'security_gaps' => $this->security_gaps,
            'weak_points' => $this->weak_points,
            'security_incidents' => $this->security_incidents,

            'entrance_gate' => $this->entrance_gate,
            'exit_gate' => $this->exit_gate,
            'emergency_gate' => $this->emergency_gate,
            'secondary_gate' => $this->secondary_gate,

            'physical_check_status' => $this->physical_check_status,
            'technical_check_status' => $this->technical_check_status,
            'security_cameras' => $this->security_cameras,

            'guards_count' => $this->guards_count,
            'active_towers_count' => $this->active_towers_count,
            'active_guard_posts_count' => $this->active_guard_posts_count,

            'security_responsible_name' => $this->security_responsible_name,
            'security_responsible_phone' => $this->security_responsible_phone,

            'other_problems' => $this->other_problems,

            'note' => $this->note,

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

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
