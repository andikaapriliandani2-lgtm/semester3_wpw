<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $_request): array
    {
        return Arr::only(parent::toArray($_request), [
            'id',
            'name',
            'email',
            'role',
        ]);
    }
}
