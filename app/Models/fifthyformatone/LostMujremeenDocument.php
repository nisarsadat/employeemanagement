<?php

namespace App\Models\fifthyformatone;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LostMujremeenDocument extends Model
{
    protected $table = 'lost_mujremeen_documents';

    protected $fillable = [
        'lost_mujremeen_id',
        'file_path',
        'original_name',
        'mime_type',
        'size',
    ];

    public function lostMujremeen(): BelongsTo
    {
        return $this->belongsTo(LostMujremeen::class, 'lost_mujremeen_id');
    }
}
