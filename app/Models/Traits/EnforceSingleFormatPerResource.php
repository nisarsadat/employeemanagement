<?php

namespace App\Models\Traits;

use App\Exceptions\ResourceAlreadyAssignedToFormatException;
use App\Models\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait EnforceSingleFormatPerResource
{
    public static function bootEnforceSingleFormatPerResource(): void
    {
        static::creating(function (Model $model) {
            static::ensureResourceCanBeUsed($model);
        });

        static::updating(function (Model $model) {
            static::ensureResourceCanBeUsedOnUpdate($model);
        });

        static::created(function (Model $model) {
            static::syncResourceOwner($model);
        });

        static::updated(function (Model $model) {
            static::syncResourceOwner($model);
        });

        static::deleted(function (Model $model) {
            static::clearResourceOwnerIfMatches($model);
        });
    }

    protected static function ensureResourceCanBeUsed(Model $model): void
    {
        if (empty($model->resource_id)) {
            return;
        }

        DB::transaction(function () use ($model) {
            $resource = Resource::query()
                ->lockForUpdate()
                ->find($model->resource_id);

            if (!$resource) {
                return;
            }

            if (!empty($resource->format_owner_type) && !empty($resource->format_owner_id)) {
                throw new ResourceAlreadyAssignedToFormatException(
                    resourceId: $resource->id,
                    existingModel: $resource->format_owner_type,
                    existingRecordId: $resource->format_owner_id,
                    existingLabel: static::formatLabel($resource->format_owner_type),
                    message: 'This resource already belongs to another single-format record.'
                );
            }
        }, 3);
    }

    protected static function ensureResourceCanBeUsedOnUpdate(Model $model): void
    {
        if (empty($model->resource_id)) {
            return;
        }

        DB::transaction(function () use ($model) {
            $resource = Resource::query()
                ->lockForUpdate()
                ->find($model->resource_id);

            if (!$resource) {
                return;
            }

            $currentModel = get_class($model);
            $currentId    = $model->getKey();

            if (
                !empty($resource->format_owner_type) &&
                !empty($resource->format_owner_id) &&
                !(
                    $resource->format_owner_type === $currentModel &&
                    (string) $resource->format_owner_id === (string) $currentId
                )
            ) {
                throw new ResourceAlreadyAssignedToFormatException(
                    resourceId: $resource->id,
                    existingModel: $resource->format_owner_type,
                    existingRecordId: $resource->format_owner_id,
                    existingLabel: static::formatLabel($resource->format_owner_type),
                    message: 'This resource already belongs to another single-format record.'
                );
            }
        }, 3);
    }

    protected static function syncResourceOwner(Model $model): void
    {
        if (empty($model->resource_id) || empty($model->getKey())) {
            return;
        }

        DB::transaction(function () use ($model) {
            $resource = Resource::query()
                ->lockForUpdate()
                ->find($model->resource_id);

            if (!$resource) {
                return;
            }

            $resource->forceFill([
                'format_owner_type' => get_class($model),
                'format_owner_id'   => $model->getKey(),
            ])->saveQuietly();
        }, 3);
    }

    protected static function clearResourceOwnerIfMatches(Model $model): void
    {
        if (empty($model->resource_id)) {
            return;
        }

        DB::transaction(function () use ($model) {
            $resource = Resource::query()
                ->lockForUpdate()
                ->find($model->resource_id);

            if (!$resource) {
                return;
            }

            if (
                $resource->format_owner_type === get_class($model) &&
                (string) $resource->format_owner_id === (string) $model->getKey()
            ) {
                $resource->forceFill([
                    'format_owner_type' => null,
                    'format_owner_id'   => null,
                ])->saveQuietly();
            }
        }, 3);
    }

    protected static function formatLabel(?string $modelClass): ?string
    {
        if (!$modelClass) {
            return null;
        }

        $labels = config('single_resource_formats.labels', []);

        return $labels[$modelClass] ?? class_basename($modelClass);
    }
}