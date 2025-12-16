<?php

namespace App\Models;

use App\Traits\HasMapPoint;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class OfficeEmployee extends Model
{
    use HasFactory;
    use HasMapPoint;

    public const ACTIVE   = 'فعال';
    public const INACTIVE = 'غیر فعال';

    // Tell the model which date columns to use
    private const EVENT_DATE_COL       = 'event_date';              // events table
    private const RESOURCE_DATE_COL    = 'resource_saved_date';     // resources table
    private const INFORMATION_DATE_COL = 'information_saved_date';  // information table

    // ✅ ADD THIS: include computed field in JSON
    protected $appends = ['picture_url'];

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
        'job_type',
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

    // ✅ ADD THIS: picture_url accessor (returns FULL URL)
    public function getPictureUrlAttribute(): ?string
    {
        if (!$this->picture) return null;

        $p = str_replace('\\', '/', trim((string) $this->picture));

        // already absolute
        if (preg_match('/^https?:\/\//i', $p)) return $p;

        // your current DB format: "uploads/...."
        if (str_starts_with($p, 'uploads/')) return asset($p);
        if (str_starts_with($p, '/uploads/')) return asset(ltrim($p, '/'));

        // storage patterns if you ever store "public/..." or "office-employees/..."
        if (str_starts_with($p, 'public/')) $p = substr($p, 7);

        return asset('storage/' . ltrim($p, '/'));
    }

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

    public function resource()
    {
        // assuming office_employees has a resource_id column
        return $this->belongsTo(Resource::class, 'resource_id', 'id');
    }

    public function informations()
    {
        return $this->hasMany(Information::class, 'office_employee_id' , 'office_employee_tadabiri_id' ,'office_employee_info_id', 'id');
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

    public function expenses()
    {
        return $this->morphMany(Expense::class, 'payee');
    }

    // ---------- Stored activity scopes ----------
    public function scopeActive(Builder $q): Builder
    {
        return $q->where('activity', self::ACTIVE);
    }

    public function scopeInactive(Builder $q): Builder
    {
        return $q->where('activity', self::INACTIVE);
    }

    /* =========================================================
     | NEW ACTIVITY LOGIC (WEEKLY THRESHOLD ON RESOURCE EVENTS)
     |
     | ACTIVE if ANY Resource of this employee has >= 5 Events
     | within the last 7 days. Else INACTIVE.
     ========================================================= */

    /**
     * Query employees who are considered ACTIVE by the weekly rule.
     *
     * Rule:
     * - An employee is active if ANY of their resources has >= $minEvents
     *   events in the last $days days.
     *
     * NOTE: This requires Resource::events() relationship to exist.
     */
    public function scopeActiveByWeeklyResourceEvents(
        Builder $q,
        int $minEvents = 5,
        int $days = 7
    ): Builder {
        $since    = now()->subDays($days);
        $eventCol = self::EVENT_DATE_COL ?: 'created_at';

        return $q->whereHas('resources', function ($r) use ($since, $eventCol, $minEvents) {
            $r->whereHas(
                'events',
                fn ($e) => $e->where($eventCol, '>=', $since),
                '>=',
                $minEvents
            );
        });
    }

    /**
     * Recalculate and STORE activity field using weekly rule.
     *
     * - set everyone inactive
     * - set active those matching weekly rule
     */
    public static function refreshStatusesByWeeklyRule(
        int $minEvents = 5,
        int $days = 7
    ): void {
        // 1) Set all inactive
        static::query()->update(['activity' => self::INACTIVE]);

        // 2) Activate those who match rule
        static::query()
            ->activeByWeeklyResourceEvents($minEvents, $days)
            ->update(['activity' => self::ACTIVE]);
    }
}
