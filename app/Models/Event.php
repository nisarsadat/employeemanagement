<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'resource_id',
        'office_employee_id',
        'event_type',
        'event_specific_type',
        'target_person',
        'criminal',
        'event_date',
        'event_hour',
        'event_zone',
        'event_hawza',
        'event_specific_place',
        'talafat_jani',
        'talafat_mali',
        'tafseel',
        'Province_addr',
        'talks',
        'coordinates',
        'note',
    ];

    // ✅ Casts (helps with "current month" filters if you use event_date)
    protected $casts = [
        'event_date' => 'date',
    ];

    // 🔗 Relations (UNCHANGED)
    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }

    public function officeEmployee()
    {
        return $this->belongsTo(OfficeEmployee::class, 'office_employee_id');
    }

    public function documents()
    {
        return $this->hasMany(EventDocument::class);
    }

    // ✅ NEW: instant flip employee to فعال when a new event is created
    protected static function booted()
    {
        static::created(function ($row) {
            foreach (['office_employee_id', 'event_office_employee_id'] as $key) {
                $empId = $row->{$key} ?? null;
                if ($empId) {
                    OfficeEmployee::where('id', $empId)
                        ->where('activity', '!=', OfficeEmployee::ACTIVE)
                        ->update(['activity' => OfficeEmployee::ACTIVE]);
                }
            }
        });
    }
}
