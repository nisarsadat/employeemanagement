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
        'office_employee_akhistonky_id', // ✅ FIXED (was office_employee_akhistonky)
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
        'injured',
        'trust_percentage',
        'killed',
        'tafseel',
        'Province_addr',
        'talks',
        'coordinates',
        'note',
        'docs_note',
    ];

    protected $casts = [
        'event_date' => 'date',
    ];

    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }

    public function officeEmployee()
    {
        return $this->belongsTo(OfficeEmployee::class, 'office_employee_id');
    }

    // ✅ NEW: the "akhistonky" employee relation
    public function akhistonkyEmployee()
    {
        return $this->belongsTo(OfficeEmployee::class, 'office_employee_akhistonky_id');
    }

    public function documents()
    {
        return $this->hasMany(EventDocument::class);
    }

    protected static function booted()
    {
        static::created(function ($row) {
            // ✅ FIXED keys
            foreach (['office_employee_id', 'office_employee_akhistonky_id'] as $key) {
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
