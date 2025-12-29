<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SundryDocument extends Model
{
    protected $fillable = [
        'outgoing_incoming_id',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
    ];

    public function outgoingIncoming(): BelongsTo
    {
        return $this->belongsTo(OutgoingIncoming::class, 'outgoing_incoming_id');
    }

    // Optional: easy url
    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }
}
