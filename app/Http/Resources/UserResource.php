<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'username'  => $this->username,
            'image_url' => $this->image ? asset('storage/'.$this->image) : null,

            // Arrays ready for the frontend
            'roles'       => $this->getRoleNames()->values()->toArray(), // ["admin", ...]
            'permissions' => $this->getAllPermissions()->pluck('name')->values()->toArray(),
        ];
    }
}
