<?php

namespace App\Services\Tracker;

use App\Services\Tracker\Contracts\TrackerServiceInterface;
use App\Services\Tracker\Entities\Tracker;
use Illuminate\Http\Client\PendingRequest;

class LinkeTrackerService implements TrackerServiceInterface
{
    public function __construct(
        protected PendingRequest $api
    )
    {
    }

    public function track(string $code): Tracker
    {
        $response = $this
            ->api
            ->get('/track/json', ['codigo' => $code])
            ->json();

        return new Tracker(
            code: $response['codigo'],
            host: $response['host'],
            updatedAt: $response['ultimo'],
            events: $response['eventos'],
        );
    }
}
