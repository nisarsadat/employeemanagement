<?php
// app/Models/Traits/EnforceSingleFormatPerResource.php
namespace App\Models\Traits;

use App\Models\ResourceFormat;
use Illuminate\Validation\ValidationException;

trait EnforceSingleFormatPerResource
{
  public static function bootEnforceSingleFormatPerResource()
  {
    // BEFORE create: block if resource already has any format
    static::creating(function ($model) {
      $rid = $model->resource_id ?? null;

      if (!$rid) {
        throw ValidationException::withMessages([
          'resource_id' => 'resource_id is required.',
        ]);
      }

      if (ResourceFormat::where('resource_id', $rid)->exists()) {
        throw ValidationException::withMessages([
          'resource_id' => 'This resource already has a 56-format record.',
        ]);
      }
    });

    // AFTER create: create the lock row
    static::created(function ($model) {
      ResourceFormat::create([
        'resource_id'      => $model->resource_id,
        'formatable_type'  => $model->getMorphClass(),
        'formatable_id'    => $model->getKey(),
      ]);
    });

    // DELETE: remove the lock row
    static::deleting(function ($model) {
      ResourceFormat::where('formatable_type', $model->getMorphClass())
        ->where('formatable_id', $model->getKey())
        ->delete();
    });
  }

  // optional relation from format record to lock
  public function formatLock()
  {
    return $this->morphOne(\App\Models\ResourceFormat::class, 'formatable');
  }
}
