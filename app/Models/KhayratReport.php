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
}
