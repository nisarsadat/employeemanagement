<?php
// app/Models/Information.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Information extends Model
{
    protected $table = 'information';

    protected $fillable = [
        'resource_id',
        'officeemployee_id',
        'zone_employee_id',

        // your “second” pair
        'information_office_employee_code',
        'infomation_zone_employee_code',

        'name',
        'father_name',
        'nickname',
        'grand_father_name',
        'main_province',
        'main_district',
        'main_village',
        'now_province',
        'now_district',
        'now_village',
        'seyasi',
        'jenayee',
        'information_date',
        'information_hour',
        'importance_degree',
        'information_category',
        'tafseel',
        'talks',
        'outcome',
        'notes',
    ];

    // Primary associations
    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class, 'resource_id');
    }

    public function officeEmployee(): BelongsTo
    {
        return $this->belongsTo(OfficeEmployee::class, 'officeemployee_id');
    }

    public function zoneEmployee(): BelongsTo
    {
        return $this->belongsTo(ZoneEmployee::class, 'zone_employee_id');
    }

    // “Information” associations (2nd pair)
    public function infoOfficeEmployee(): BelongsTo
    {
        return $this->belongsTo(OfficeEmployee::class, 'information_office_employee_code');
    }

    public function infoZoneEmployee(): BelongsTo
    {
        return $this->belongsTo(ZoneEmployee::class, 'infomation_zone_employee_code');
    }

    // Documents
    public function documents(): HasMany
    {
        return $this->hasMany(InformationDocument::class, 'information_id');
    }
}
