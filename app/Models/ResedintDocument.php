<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class ResedintDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'resedint_id',
        'file_name',
        'file_path',
        'mime_type',
        'size_bytes',
    ];

    public function resedint()
    {
        return $this->belongsTo(Resedint::class);
    }

    protected static function booted()
    {
        static::deleting(function ($doc) {
            if ($doc->file_path) {
                Storage::disk('public')->delete($doc->file_path);
            }
        });
    }
     public function getUrlAttribute(): ?string
    {
        return $this->file_path ? Storage::disk('public')->url($this->file_path) : null;
    }
}
