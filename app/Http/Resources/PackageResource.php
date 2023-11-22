<?php

namespace App\Http\Resources;

use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Package */
class PackageResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'code' => $this->code,
            'icon' => $this->meta->icon,
            'alias' => $this->meta->alias,
            'lastEventAt' => $this->updated_at,
            'events' => $this->events,
        ];
    }
}
