<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaptiveDeliveryDocument extends Model
{
    protected $table = 'captive_delivery_documents';

    protected $fillable = [
        'captive_delivery_id',
        'file_name',
        'file_path',
        'mime_type',
        'size_bytes',
    ];

    public function captiveDelivery(): BelongsTo
    {
        return $this->belongsTo(CaptiveDelivery::class, 'captive_delivery_id');
    }
}
