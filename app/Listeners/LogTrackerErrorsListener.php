<?php

namespace App\Listeners;

use Illuminate\Http\Client\Events\ConnectionFailed;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LogTrackerErrorsListener
{
    public function handle(ConnectionFailed $event): void
    {
        Log::driver('tracker')
            ->error(
                "Error in tracking package request",
                [
                    'method' => $event->request->method(),
                    'url' => $event->request->url(),
                    'user' => Auth::user(),
                ]
            );
    }
}
