<?php
// app/Models/Traits/EnforceSingleFormatPerResource.php

namespace App\Models\Traits;

use Illuminate\Validation\ValidationException;

trait EnforceSingleFormatPerResource
{
    public static function bootEnforceSingleFormatPerResource()
    {
        static::creating(function ($model) {
            $rid = $model->resource_id ?? null;

            if (!$rid) {
                throw ValidationException::withMessages([
                    'resource_id' => 'resource_id is required.',
                ]);
            }

            // ✅ Enforce: only ONE row per resource_id in THIS model/table
            if ($model::where('resource_id', $rid)->exists()) {
                throw ValidationException::withMessages([
                    'resource_id' => 'دا سرچینه له مخکې نه د همدې برخې ریکارډ لري.',
                ]);
            }
        });
    }
}