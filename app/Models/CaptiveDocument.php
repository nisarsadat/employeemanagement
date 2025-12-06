<?php

namespace App\Models;

use App\Models\fifthyFormats\Captive;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CaptiveDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'captive_id',
        'file_name',
        'file_path',
        'mime_type',
        'size_bytes',
    ];

    public function captive()
    {
        return $this->belongsTo(Captive::class);
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
