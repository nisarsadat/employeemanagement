<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventSpecificType extends Model
{
    protected $fillable = ['event_type_id', 'name'];

    public function eventType(): BelongsTo
    {
        return $this->belongsTo(\App\Models\EventType::class, 'event_type_id');
    }
}
