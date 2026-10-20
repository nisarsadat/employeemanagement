<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_title',
        'organ',
        'province',
        'district',
        'specific_place',
        'coordinates',
        'start_date',
        'end_date',
        'total_time',
        'participant',
        'boss_participant',
        'office_employee_count',
        'leader_name',
        'done_actions',
        'mashkok_situations',
        'problems',
        'meeting_supplier',
        'meeting_supplier_phone',
        'meeting_amniyat_responsable',
        'meeting_amniyat_responsable_phone',
        'amniyat_mansobine_count',
        'note',
        'message_to_finance',
        'money_amount_words',
        'finance_status',
        'finance_paid_at',
        'account_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'office_employee_count' => 'integer',
        'amniyat_mansobine_count' => 'integer',
        'finance_paid_at' => 'datetime',
    ];

    public function documents()
    {
        return $this->hasMany(MeetingDocument::class);
    }

    public function finances(): HasMany
    {
        return $this->hasMany(Finance::class, 'meeting_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    /**
     * ✅ ترسره کوونکي — office employees who performed this meeting.
     */
    public function officeEmployees()
    {
        return $this->belongsToMany(
            OfficeEmployee::class,
            'meeting_office_employee'
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