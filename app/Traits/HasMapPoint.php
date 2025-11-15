<?php 
// app/Models/Traits/HasMapPoint.php

namespace App\Traits;

use App\Models\MapPoint;

trait HasMapPoint
{
    public static function bootHasMapPoint()
    {
        static::saved(function ($model) {
            // if no coordinates → remove previous point if exists
            if (empty($model->coordinates)) {
                MapPoint::where('subject_type', get_class($model))
                    ->where('subject_id', $model->id)
                    ->delete();
                return;
            }

            // map basic fields (adapt if your model uses different names)
            $data = [
                'name'        => $model->name ?? '',
                'father_name' => $model->father_name ?? null,
                'code_number' => $model->code_number ?? null,
                'coordinates' => $model->coordinates,
                'phone'       => $model->phone_number ?? $model->phone ?? null,
                'note'        => $model->note ?? null,
                // if model has picture field and you want its avatar on map:
                'picture_path'=> $model->picture_path ?? null,
            ];

            MapPoint::updateOrCreate(
                [
                    'subject_type' => get_class($model),
                    'subject_id'   => $model->id,
                ],
                $data
            );
        });

        static::deleted(function ($model) {
            MapPoint::where('subject_type', get_class($model))
                ->where('subject_id', $model->id)
                ->delete();
        });
    }

    public function mapPoint()
    {
        return $this->morphOne(MapPoint::class, 'subject');
    }
}
