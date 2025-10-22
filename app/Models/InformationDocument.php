<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InformationDocument extends Model
{
    protected $fillable = [
        'information_id',
        'file_name',
        'file_path',
        'mime_type',
        'size_bytes',
    ];

    public function information(): BelongsTo
    {
        return $this->belongsTo(Information::class, 'information_id');
    }
}
