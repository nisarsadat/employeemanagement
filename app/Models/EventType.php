<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventType extends Model
{
    protected $fillable = ['name'];

    // ✅ EventType has many EventSpecificType
    public function specificTypes(): HasMany
    {
        return $this->hasMany(\App\Models\EventSpecificType::class, 'event_type_id');
    }
}
