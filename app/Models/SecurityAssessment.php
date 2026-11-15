<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecurityAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'place_name',
        'responsible_owner_name',
        'province',
        'district',
        'specific_place',
        'coordinates',
        'north',
        'south',
        'east',
        'west',
        'daily_visitors_count',
        'risk_places',
        'vulnerable_places',
        'security_gaps',
        'weak_points',
        'security_incidents',
        'entrance_gate',
        'exit_gate',
        'emergency_gate',
        'secondary_gate',
        'physical_check_status',
        'technical_check_status',
        'security_cameras',
        'guards_count',
        'active_towers_count',
        'active_guard_posts_count',
        'security_responsible_name',
        'security_responsible_phone',
        'other_problems',
        'note',
    ];

    protected $casts = [
        'daily_visitors_count' => 'integer',
        'guards_count' => 'integer',
        'active_towers_count' => 'integer',
        'active_guard_posts_count' => 'integer',
    ];

    /**
     * ✅ ترسره کوونکي — office employees who performed this assessment.
     */
    public function documents()
    {
        return $this->hasMany(SecurityAssessmentDocument::class);
    }

    public function officeEmployees()
    {
        return $this->belongsToMany(
            OfficeEmployee::class,
            'security_assessment_office_employee'
        )->withTimestamps();
    }

    public function getOfficeEmployeesDisplayAttribute(): string
    {
        if (!$this->relationLoaded('officeEmployees')) {
            return '';
        }

        return $this->officeEmployees
            ->map(function ($employee) {
                $name = trim((string) $employee->name);
                $code = trim((string) $employee->code_number);

                if ($name !== '' && $code !== '') {
                    return $name.' ('.$code.')';
                }

                return $name !== '' ? $name : $code;
            })
            ->filter()
            ->implode('، ');
    }
}
