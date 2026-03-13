<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'image' => $this->image,
            'image_url' => $this->image ? asset('storage/' . $this->image) : null,
            'roles' => $this->getRoleNames()->values()->toArray(),
            'permissions' => $this->getAllPermissions()->pluck('name')->values()->toArray(),
        ];
    }
}