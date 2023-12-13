<?php

namespace App\Http\Resources;

use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/** @mixin Package */
#[OA\Schema(
    schema: 'PackageResource',
    properties: [
        new OA\Property(property: 'code', description: 'Package code', type: 'string', example: 'NL718729417BR'),
        new OA\Property(property: 'icon', description: 'Package icon', type: 'string', example: 'clothing'),
        new OA\Property(property: 'alias', type: 'string', example: 'My favorite package'),
        new OA\Property(property: 'lastEventAt', type: 'string', example: '2022-08-01T00:00:00.000000Z'),
        new OA\Property(
            property: 'events',
            description: 'Package events',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/EventResource')
        )
    ]
)]
class PackageResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'code' => $this->resource->code,
            'icon' => $this->resource->meta->icon,
            'alias' => $this->resource->meta->alias,
            'lastEventAt' => $this->resource->updated_at,
            'events' => EventResource::collection($this->resource->events),
        ];
    }
}
