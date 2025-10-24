<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /** @param Request $request */
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'username'   => $this->username,
            'photo_url'  => $this->photo_url, // from accessor
            'active'     => (bool) $this->active,
            'roles'      => $this->whenLoaded('roles', fn() => $this->getRoleNames()),
            'permissions'=> $this->whenLoaded('permissions', fn() => $this->getPermissionNames()),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
