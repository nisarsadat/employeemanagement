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

        'tafseel',
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
    // Information.php

    public function finances()
    {
        return $this->hasMany(Finance::class, 'information_id');
    }

    /* =========================================================
     | AUTO-ACTIVATE EMPLOYEE ON CREATE
     ========================================================= */

    protected static function booted()
    {
        static::created(function ($row) {
            if ($row->office_employee_id) {
                OfficeEmployee::whereKey($row->office_employee_id)
                    ->where('activity', '!=', OfficeEmployee::ACTIVE)
                    ->update(['activity' => OfficeEmployee::ACTIVE]);
            }
        });
    }
    public function officeEmployeeInfo(): BelongsTo
    {
        return $this->belongsTo(OfficeEmployee::class, 'office_employee_info_id', 'id');
    }
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }



}
