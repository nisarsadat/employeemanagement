<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class OfficeEmployee extends Model
{
    use HasFactory;

    // ... top of the class
    public const ACTIVE   = 'فعال';
    public const INACTIVE = 'غیر فعال';

    // Tell the model which date columns to use
    private const EVENT_DATE_COL       = 'event_date';              // events table
    private const RESOURCE_DATE_COL    = 'resource_saved_date';     // resources table
    private const INFORMATION_DATE_COL = 'information_saved_date';  // information table


    protected $fillable = [
        // Media
        'picture',

        // Identity
        'name',
        'father_name',
        'grand_father_name',
        'nick_name',
        'Province_addr',
        'code_number',

        // Rank/Levels & background
        'bast',
        'level',
        'history_jahad',
        'dilgay_mesher',
        'jahadi_mawaqaf',
        'jahad_time_responsibility',
        'tazkira',

        // Places
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

        // Job & org
        'specific_job',
        'qisem',
        'zone',
        'hawza',

        // Status & education
        'madani_halat',
        'activity',         // 'فعال' | 'غیر فعال'
        'relegy_study',
        'new_study',
        'faculty',
        'nizami',
        'gun_knowledge',

        // Demographic
        'age',
        'specific_skills',
        'language',
        'nation',

        // Contact & misc
        'phone_number',
        'coordinates',
        'join_date',
        'left_date',
        'note',
    ];

    // ---------- Search ----------
    public function scopeSearch($query, $search)
    {
        if (!$search) return $query;

        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('father_name', 'like', "%{$search}%")
              ->orWhere('grand_father_name', 'like', "%{$search}%")
              ->orWhere('nick_name', 'like', "%{$search}%")
              ->orWhere('phone_number', 'like', "%{$search}%")
              ->orWhere('tazkira', 'like', "%{$search}%")
              ->orWhere('code_number', 'like', "%{$search}%")
              ->orWhere('main_province', 'like', "%{$search}%")
              ->orWhere('now_province', 'like', "%{$search}%");
        });
    }

    // ---------- Relationships ----------
    public function resources()
    {
        return $this->hasMany(Resource::class, 'office_employee_id');
    }

    public function informations()
    {
        return $this->hasMany(Information::class , 'office_employee_id');
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'office_employee_id');
    }

    public function assignedEvents()
    {
        return $this->hasMany(Event::class, 'event_office_employee_id');
    }

    public function documents()
    {
        return $this->hasMany(OfficeEmployeeDocument::class);
    }
    public function expenses() { return $this->morphMany(Expense::class, 'payee'); }

    // ---------- Activity logic ----------
    public function scopeActive(Builder $q): Builder
    {
        return $q->where('activity', self::ACTIVE);
    }

    public function scopeInactive(Builder $q): Builder
    {
        return $q->where('activity', self::INACTIVE);
    }

   // ✅ REPLACE your scopeHasActivitySince with this version
    public function scopeHasActivitySince(\Illuminate\Database\Eloquent\Builder $q, \Carbon\Carbon $since): \Illuminate\Database\Eloquent\Builder
    {
        $eventCol       = self::EVENT_DATE_COL       ?: 'created_at';
        $resourceCol    = self::RESOURCE_DATE_COL    ?: 'created_at';
        $informationCol = self::INFORMATION_DATE_COL ?: 'created_at';

        return $q->where(function ($qq) use ($since, $eventCol, $resourceCol, $informationCol) {
            $qq->whereHas('events', fn($e) => $e->where($eventCol, '>=', $since))
            // ❌ removed assignedEvents because there is no event_office_employee_id column
            ->orWhereHas('resources', fn($r) => $r->where($resourceCol, '>=', $since))
            ->orWhereHas('informations', fn($i) => $i->where($informationCol, '>=', $since));
        });
    }


    /** Flip to غیر فعال, then set فعال if activity exists in the window */
    public static function refreshStatusesSince(Carbon $since): void
    {
        static::query()
            ->where('activity', '!=', self::INACTIVE)
            ->update(['activity' => self::INACTIVE]);

        static::query()
            ->hasActivitySince($since)
            ->update(['activity' => self::ACTIVE]);
    }
}
