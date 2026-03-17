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
        'office_employee_info_id',

        'name',
        'nick_name',
        'father_name',
        'grand_father_name',
        'Province_addr',
        'madani_halat',
        'age',
        'job',
        'estijalyat_degree',
        'main_province',
        'main_districts',
        'main_village',

        'hamahangi_date',
        'maktop_number',
        'organ',

        'now_province',
        'now_district',
        'now_village',

        'syasi_info_type',
        'jenayee_info_type',

        'information_saved_date',
        'information_saved_hour',
        'information_degree',
        'information_category',
        'trust_percentage',
        'resource_lasrasy',

        'specefic_place',
        'home_number',
        'street_number',

        'tafseel',
        'coordinates',
        'tahlil',
        'manager_comment',
        'lead_drirection',
        'talks',
        'note',
        'docs_note',
        'result',
        'message_to_finance',
        'money_amount_words',
        'finance_status',
        'finance_paid_at',
        'account_id',
    ];

    protected $casts = [
        'information_saved_date' => 'date',
        'finance_paid_at' => 'datetime',
    ];

    /* =========================================================
     | RELATIONSHIPS
     ========================================================= */

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class, 'resource_id', 'id');
    }

    public function officeEmployee(): BelongsTo
    {
        return $this->belongsTo(OfficeEmployee::class, 'office_employee_id', 'id');
    }

    public function tadabiriEmployee(): BelongsTo
    {
        return $this->belongsTo(OfficeEmployee::class, 'office_employee_tadabiri_id', 'id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(InformationDocument::class, 'information_id', 'id');
    }

    public function finances(): HasMany
    {
        return $this->hasMany(Finance::class, 'information_id');
    }

    public function officeEmployeeInfo(): BelongsTo
    {
        return $this->belongsTo(OfficeEmployee::class, 'office_employee_info_id', 'id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    /**
     * The matched/original information record.
     */
    public function matchedInformation(): BelongsTo
    {
        return $this->belongsTo(self::class, 'matched_information_id', 'id');
    }

    /**
     * All information rows that point to this row.
     */
    public function childMatchedInformations(): HasMany
    {
        return $this->hasMany(self::class, 'matched_information_id', 'id');
    }

    /* =========================================================
     | AUTO-ACTIVATE EMPLOYEE ON CREATE
     | AUTO-MATCH INFORMATION BEFORE SAVE
     ========================================================= */

    protected static function booted()
    {
        static::saving(function (self $row) {
            $row->matched_information_id = static::resolveMatchedInformationId($row);
        });

        static::created(function ($row) {
            if ($row->office_employee_id) {
                OfficeEmployee::whereKey($row->office_employee_id)
                    ->where('activity', '!=', OfficeEmployee::ACTIVE)
                    ->update(['activity' => OfficeEmployee::ACTIVE]);
            }
        });
    }

    /**
     * Find an older information row with the exact same:
     * - name
     * - father_name
     * - main_province
     * - main_districts
     * - main_village
     *
     * If found, return its id. Otherwise return null.
     */
    protected static function resolveMatchedInformationId(self $row): ?int
    {
        $name = $row->name;
        $fatherName = $row->father_name;
        $mainProvince = $row->main_province;
        $mainDistricts = $row->main_districts;
        $mainVillage = $row->main_village;

        // If any required matching field is empty, do not create relation
        if (
            is_null($name) || trim((string) $name) === '' ||
            is_null($fatherName) || trim((string) $fatherName) === '' ||
            is_null($mainProvince) || trim((string) $mainProvince) === '' ||
            is_null($mainDistricts) || trim((string) $mainDistricts) === '' ||
            is_null($mainVillage) || trim((string) $mainVillage) === ''
        ) {
            return null;
        }

        $query = static::query()
            ->where('name', $name)
            ->where('father_name', $fatherName)
            ->where('main_province', $mainProvince)
            ->where('main_districts', $mainDistricts)
            ->where('main_village', $mainVillage);

        // Exclude current row during update
        if ($row->exists && $row->id) {
            $query->where('id', '!=', $row->id);
        }

        // Pick the oldest matching row as the "original"
        $matched = $query->orderBy('id', 'asc')->first();

        return $matched?->id;
    }
}