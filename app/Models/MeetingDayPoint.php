<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingDayPoint extends Model
{
    protected $fillable = [
        'office_employee_id',
        'point_date',
        'points',
    ];

    protected $casts = [
        'point_date' => 'date',
        'points' => 'integer',
    ];

    public function officeEmployee(): BelongsTo
    {
        return $this->belongsTo(OfficeEmployee::class);
    }
}
