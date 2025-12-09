<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray($request)
    {
        $n      = $this->resource;
        $user   = $n->user ?? null;   // receiver
        $actor  = $n->actor ?? null;  // actor

        return [
            'id'       => $n->id,
            'module'   => $n->module,
            'action'   => $n->action,
            'level'    => $n->level,
            'title'    => $n->title,
            'message'  => $n->message,
            'changes'  => $n->changes,

            'route'    => [
                'name'   => $n->route_name,
                'params' => $n->route_params ?? [],
            ],

            'is_read'  => $n->is_read,
            'read_at'  => optional($n->read_at)->toDateTimeString(),

            'created_at'       => optional($n->created_at)->toDateTimeString(),
            'created_at_human' => optional($n->created_at)->diffForHumans(),

            // Receiver
            'user' => [
                'id'    => optional($user)->id,
                'name'  => optional($user)->name,
                'email' => optional($user)->email,
            ],
            'user_name' => optional($user)->name,

            // Actor (who did the action)
            'actor' => [
                'id'    => optional($actor)->id,
                'name'  => optional($actor)->name,
                'email' => optional($actor)->email,
            ],
            'actor_name' => optional($actor)->name,
        ];
    }
}
