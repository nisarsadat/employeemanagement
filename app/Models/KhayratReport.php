<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class KhayratReport extends Model
{
    protected $fillable = [
        'resource_id',
        'office_employee_id',
        'zone',
        'hawza',
        'specefic_place',
        'saved_date',
    ];

    protected $casts = [
        'saved_date' => 'date',
    ];

    public function resource()
    {
        return $this->belongsTo(Resource::class, 'resource_id', 'id');
    }

    public function officeEmployee()
    {
        return $this->belongsTo(OfficeEmployee::class, 'office_employee_id', 'id');
    }

    public function scopeSearch(Builder $query, string $search = null): Builder
    {
        $s = trim((string) $search);
        if ($s === '') return $query;

        return $query->where(function ($q) use ($s) {
            $q->where('zone', 'like', "%{$s}%")
              ->orWhere('hawza', 'like', "%{$s}%")
              ->orWhere('specefic_place', 'like', "%{$s}%")
              ->orWhereDate('saved_date', $s);
        });
    }

    protected static function booted()
    {
        static::created(function ($row) {
            if ($row->office_employee_id) {
                OfficeEmployee::whereKey($row->office_employee_id)
                    ->where('activity', '!=', OfficeEmployee::ACTIVE)
                    ->update(['activity' => OfficeEmployee::ACTIVE]);
            }

            if ($row->resource_id && $row->resource) {
                $row->resource->syncActivityFromRecentRecords();
            }
        });

        static::updated(function ($row) {
            if ($row->resource_id && $row->resource) {
                $row->resource->syncActivityFromRecentRecords();
            }
        });

        static::deleted(function ($row) {
            if ($row->resource_id) {
                $resource = Resource::find($row->resource_id);
                if ($resource) {
                    $resource->syncActivityFromRecentRecords();
                }
            }
        });
    }
}