<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use OpenApi\Attributes as OA;

/** @see \App\Models\Package */
#[OA\Schema(
    schema: 'PackageCollection',
    properties: [
        new OA\Property(
            property: 'packages',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/PackageResource')
        ),
        new OA\Property(
            property: 'meta',
            ref: '#/components/schemas/PaginationMeta',
            type: 'object'
        ),
        new OA\Property(
            property: 'links',
            ref: '#/components/schemas/PaginationLinks',
            type: 'object'
        )
    ],
    type: 'object'
)]
class PackageCollection extends ResourceCollection
{
    public static $wrap = 'packages';
    public function toArray(Request $request): array
    {
        return $this->collection->toArray();
    }
}
