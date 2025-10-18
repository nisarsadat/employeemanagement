<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class ZoneEmployeeDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'zone_employee_id',
        'file_name',
        'file_path',
        'mime_type',
        'size_bytes',
    ];

    public function zoneEmployee()
    {
        return $this->belongsTo(ZoneEmployee::class);
    }

    protected static function booted()
    {
        static::deleting(function ($doc) {
            if ($doc->file_path) {
                Storage::disk('public')->delete($doc->file_path);
            }
        });
    }
}
