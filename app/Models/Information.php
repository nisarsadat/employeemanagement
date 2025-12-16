<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Information extends Model
{
    protected $table = 'information';

    protected $fillable = [
        'resource_id',
        'office_employee_id',
        'office_employee_tadabiri_id',
        'office_employee_info_id',

        'name',
        'nick_name',
        'father_name',
        'grand_father_name',
        'Province_addr',

        'main_province',
        'main_districts',
        'main_village',

        'now_province',
        'now_district',
        'now_village',

        'syasi_info_type',
        'jenayee_info_type',

        'information_saved_date',
        'information_saved_hour',
        'information_degree',

        'tafseel',
        'manager_comment',
        'lead_drirection',
        'talks',
        'note',
        'result',
    ];

    // ✅ Casts (helps with month-based checks if you use information_saved_date)
    protected $casts = [
        'information_saved_date' => 'date',
    ];

    // 🧭 Relations
    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class, 'resource_id');
    }

    public function officeEmployee(): BelongsTo
    {
        return $this->belongsTo(OfficeEmployee::class, 'office_employee_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(InformationDocument::class, 'information_id');
    }

    // ✅ NEW: instant flip employee to فعال when a new information row is created
    protected static function booted()
    {
        static::created(function ($row) {
            $empId = $row->office_employee_id ?? null;
            if ($empId) {
                OfficeEmployee::where('id', $empId)
                    ->where('activity', '!=', OfficeEmployee::ACTIVE)
                    ->update(['activity' => OfficeEmployee::ACTIVE]);
            }
        });
    }
}
