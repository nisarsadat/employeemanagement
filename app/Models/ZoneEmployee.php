<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ZoneEmployee extends Model
{
    use HasFactory;

    protected $fillable = [
        'picture',
        'name',
        'father_name',
        'nickname',
        'code_number',
        'hawza',
        'zone',
        'degree',
        'zone_entry_hour',
        'notes',
    ];

    public function scopeSearch($query, $search)
    {
        if (!$search)
            return $query;

        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('father_name', 'like', "%{$search}%")
                ->orWhere('nickname', 'like', "%{$search}%")
                ->orWhere('zone', 'like', "%{$search}%")
                ->orWhere('code_number', 'like', "%{$search}%")
                ->orWhere('degree', 'like', "%{$search}%");
        });
    }



    public function documents()
    {
        return $this->hasMany(ZoneEmployeeDocument::class);
    }


}
