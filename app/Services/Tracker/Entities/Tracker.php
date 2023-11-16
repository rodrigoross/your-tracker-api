<?php

namespace App\Services\Tracker\Entities;

use App\Services\Tracker\Enums\TrackingStatus;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class Tracker
{
    public string $code;
    public string $host;
    public array $events;
    public Carbon $updatedAt;

    public function __construct(string $code, string $host, string $updatedAt, array $events)
    {
        $this->code = $code;
        $this->host = $host;
        $this->updatedAt = Carbon::parse($updatedAt);

        $this->events = collect($events)
            ->map(fn($event) => new Event(
                datetime: \Carbon\Carbon::createFromFormat("d/m/Y H:i", "{$event['data']} {$event['hora']}"),
                location: $event['local'],
                status: TrackingStatus::fromValue($event['status']),
                message: $event['status'],
                subStatus: $event['subStatus']
            ))->sortByDesc('datetime')
            ->toArray();
    }
}
