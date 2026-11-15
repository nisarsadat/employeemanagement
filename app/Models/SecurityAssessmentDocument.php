<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class SecurityAssessmentDocument extends Model
{
    protected $fillable = [
        'security_assessment_id',
        'file_name',
        'file_path',
        'mime_type',
        'size_bytes',
    ];

    public function securityAssessment(): BelongsTo
    {
        return $this->belongsTo(SecurityAssessment::class);
    }

    protected static function booted(): void
    {
        static::deleting(function ($document) {
            if ($document->file_path) {
                Storage::disk('public')->delete($document->file_path);
            }
        });
    }
}
