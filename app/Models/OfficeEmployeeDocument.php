<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class OfficeEmployeeDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'office_employee_id',
        'file_name',
        'file_path',
        'mime_type',
        'size_bytes',
    ];

    public function officeEmployee()
    {
        return $this->belongsTo(OfficeEmployee::class);
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
