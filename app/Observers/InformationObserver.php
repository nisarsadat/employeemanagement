<?php

namespace App\Observers;

use App\Models\Information;
use App\Support\InfoTypeMapper;
use Illuminate\Support\Arr;

class InformationObserver
{
    public function saved(Information $info): void
    {
        $rawTypes = array_filter([
            $info->syasi_info_type,
            $info->jenayee_info_type,
        ]);

        if (!$rawTypes) return;

        $types = array_unique(array_map(fn($t) => InfoTypeMapper::normalize($t), $rawTypes));

        $map = [
            'saeenfasad' => \App\Models\fifthyFormats\SaeenFasad::class,
            'fasad' => \App\Models\fifthyFormats\Fasad::class,
            'jawasis' => \App\Models\fifthyFormats\Jawasis::class,
            'robber' => \App\Models\fifthyformatone\Robbers::class,
            'mawads' => \App\Models\fifthyformatone\Mawad::class,
            'heists' => \App\Models\fifthyFormats\Heist::class,
            'noneresposablepeole' => \App\Models\fifthyformatone\NonResponsablePeople::class,
            'badmashano' => \App\Models\fifthyformatsthree\Badmashano::class,
            'luchakano' => \App\Models\fifthyformatone\Luchakano::class,
            'mahajeerat' => \App\Models\fifthyFormats\Mahajerat::class,
            'mashkok_homes' => \App\Models\fifthyformatone\MashkokHomes::class,
            'mashkok_shops' => \App\Models\fifthyformatone\MashkokShops::class,
            'mashkok_people' => \App\Models\fifthyformatsthree\MashkokPeople::class,
            'captives' => \App\Models\fifthyFormats\Captive::class,
        ];

        foreach ($types as $type) {
            if (!isset($map[$type])) continue;

            $targetClass = $map[$type];
            $target = new $targetClass;

            $common = array_values(array_intersect($info->getFillable(), $target->getFillable()));
            $data = Arr::only($info->toArray(), $common);

            // link back
            $data['information_id'] = $info->id;

            $targetClass::updateOrCreate(
                ['information_id' => $info->id],
                $data
            );
        }
    }
}