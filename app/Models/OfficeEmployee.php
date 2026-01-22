<?php

namespace App\Models;

use App\Traits\HasMapPoint;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class OfficeEmployee extends Model
{
    use HasFactory;
    use HasMapPoint;

    public const ACTIVE   = 'فعال';
    public const INACTIVE = 'غیر فعال';

    protected $table = 'office_employees';

    protected $fillable = [
        'picture',

        'name',
        'father_name',
        'grand_father_name',
        'nick_name',
        'Province_addr',
        'code_number',

        'bast',
        'level',
        'history_jahad',
        'dilgay_mesher',
        'jahadi_mawaqaf',
        'jahad_time_responsibility',
        'tazkira',

        'jahad_place',
        'main_province',
        'main_district',
        'main_village',

        'now_province',
        'now_district',
        'now_village',
        'now_specific_place',
        'now_street_number',
        'now_home_number',

        'specific_job',
        'qisem',
        'zone',
        'hawza',        
        'makhafat',
        'makafat',

        'madani_halat',
        'activity',
        'relegy_study',
        'new_study',
        'faculty',
        'nizami',
        'job_type',
        'gun_knowledge',

        'age',
        'specific_skills',
        'language',
        'nation',

        'phone_number',
        'coordinates',
        'join_date',
        'left_date',
        'note',
    ];

    /* =========================================================
     | ACCESSORS
     ========================================================= */

    protected $appends = ['picture_url'];

    public function getPictureUrlAttribute(): ?string
    {
        if (!$this->picture) return null;

        $p = str_replace('\\', '/', $this->picture);

        if (preg_match('/^https?:\/\//i', $p)) return $p;
        if (str_starts_with($p, 'uploads/')) return asset($p);
        if (str_starts_with($p, '/uploads/')) return asset(ltrim($p, '/'));
        if (str_starts_with($p, 'public/')) $p = substr($p, 7);

        return asset('storage/' . ltrim($p, '/'));
    }

    /* =========================================================
     | SEARCH
     ========================================================= */

    public function scopeSearch($q, $search)
    {
        if (!$search) return $q;

        return $q->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('father_name', 'like', "%{$search}%")
              ->orWhere('grand_father_name', 'like', "%{$search}%")
              ->orWhere('nick_name', 'like', "%{$search}%")
              ->orWhere('phone_number', 'like', "%{$search}%")
              ->orWhere('code_number', 'like', "%{$search}%")
              ->orWhere('tazkira', 'like', "%{$search}%");
        });
    }

    /* =========================================================
     | RELATIONSHIPS (ALL CORRECT)
     ========================================================= */

    public function resources()
    {
        return $this->hasMany(Resource::class, 'office_employee_id', 'id');
    }

    public function resource()
    {
        return $this->belongsTo(Resource::class, 'resource_id', 'id');
    }

    public function informations()
    {
        return $this->hasMany(Information::class, 'office_employee_id', 'id');
    }

    public function tadabiriInformations()
    {
        return $this->hasMany(Information::class, 'office_employee_tadabiri_id', 'id');
    }

    public function infoInformations()
    {
        return $this->hasMany(Information::class, 'office_employee_info_id', 'id');
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'office_employee_id', 'id');
    }

    public function assignedEvents()
    {
        return $this->hasMany(Event::class, 'event_office_employee_id', 'id');
    }

    public function documents()
    {
        return $this->hasMany(OfficeEmployeeDocument::class, 'office_employee_id', 'id');
    }

    public function finances()
    {
        return $this->hasMany(Finance::class, 'office_employee_id', 'id');
    }

    /* =========================================================
     | ACTIVITY SCOPES (NEW LOGIC)
     | ACTIVE if has >= 100 active resources
     | INACTIVE if has < 100 active resources
     ========================================================= */

    public function scopeActive(Builder $q): Builder
    {
        return $q->whereHas(
            'resources',
            fn ($rq) => $rq->where('activity', self::ACTIVE),
            '>=',
            100
        );
    }

    public function scopeInactive(Builder $q): Builder
    {
        return $q->whereHas(
            'resources',
            fn ($rq) => $rq->where('activity', self::ACTIVE),
            '<',
            100
        );
    }
}
