<?php

namespace App\Observers;

use App\Models\Event;
use App\Models\fifthyformatfour\CriminalCapturePlace;
use Illuminate\Support\Arr;

class EventObserver
{
    public function saved(Event $event): void
    {
        $matchValues = [
            'هغو ساحو کې چې مجرمین پکې نیول شوي',
            'هغو ساحو کې چې مجرمین نیول شوي',
            // add other Dari/Pashto variants here
        ];

        $eventType = trim((string) $event->event_type);
        $speceficType = trim((string) $event->event_specific_type);

        $shouldSync =
            in_array($eventType, $matchValues, true) ||
            in_array($speceficType, $matchValues, true);

        if (!$shouldSync) {
            return;
        }

        $target = new CriminalCapturePlace();

        $common = array_values(array_intersect(
            $event->getFillable(),
            $target->getFillable()
        ));

        $data = Arr::only($event->toArray(), $common);

        $data['event_id'] = $event->id;

        CriminalCapturePlace::updateOrCreate(
            ['event_id' => $event->id],
            $data
        );
    }

    public function deleted(Event $event): void
    {
        CriminalCapturePlace::where('event_id', $event->id)->delete();
    }
}