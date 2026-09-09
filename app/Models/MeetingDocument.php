<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MeetingDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_id',
        'file_name',
        'file_path',
        'mime_type',
        'size_bytes',
    ];

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }

    protected static function booted()
    {
        static::deleting(function ($document) {
            if ($document->file_path) {
                Storage::disk('public')->delete($document->file_path);
            }
        });
    }
}