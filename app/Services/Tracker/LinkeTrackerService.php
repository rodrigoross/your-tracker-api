<?php

namespace App\Services\Tracker;

use App\Services\Tracker\Contracts\TrackerServiceInterface;
use App\Services\Tracker\Entities\Tracker;
use App\Services\Tracker\Exceptions\PackageIsNotPostedException;
use Illuminate\Http\Client\PendingRequest;

class LinkeTrackerService implements TrackerServiceInterface
{
    public function __construct(
        protected PendingRequest $api
    )
    {
    }

    /**
     * @throws PackageIsNotPostedException
     */
    public function track(string $code): Tracker
    {
        $response = $this
            ->api
            ->get('/track/json', ['codigo' => $code]);

        if ($response->clientError()) {
            throw new PackageIsNotPostedException("{$code} is not posted yet");
        }

        $data = $response->json();

        return new Tracker(
            code: $data['codigo'],
            host: $data['host'],
            lastEventAt: $data['ultimo'],
            events: $data['eventos'],
        );
    }
}
