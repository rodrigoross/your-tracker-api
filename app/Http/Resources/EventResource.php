<?php

namespace App\Http\Resources;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/** @mixin Event */
#[OA\Schema(
    schema: 'EventResource',
    properties: [
        new OA\Property(
            property: 'datetime',
            description: 'Event datetime',
            type: 'string',
            format: 'date-time',
            example: '2022-08-01T00:00:00.000000Z'
        ),
        new OA\Property(
            property: 'location',
            description: 'Event location',
            type: 'string',
            example: 'São Paulo, SP'
        ),
        new OA\Property(
            property: 'status',
            description: 'Event status',
            type: 'string',
            example: 'POSTED'
        ),
        new OA\Property(
            property: 'message',
            description: 'Event message',
            type: 'string',
            example: 'Objeto postado'
        )
    ]
)]
class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'datetime' => $this->datetime,
            'location' => $this->location,
            'status' => $this->status,
            'message' => $this->message,
            'subStatus' => $this->subStatus,
        ];
    }
}
