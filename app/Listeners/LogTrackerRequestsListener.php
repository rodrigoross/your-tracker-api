<?php

namespace App\Listeners;

use Illuminate\Http\Client\Events\RequestSending;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LogTrackerRequestsListener
{
    public function handle(RequestSending $event): void
    {
        Log::driver('tracker')
            ->info(
                "Tracking package request",
                [
                    'method' => $event->request->method(),
                    'url' => $event->request->url(),
                    'user'=> Auth::user(),
                ]
            );
    }
}
