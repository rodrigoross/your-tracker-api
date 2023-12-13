<?php

namespace App\Jobs\Package;

use App\Models\Package;
use App\Notifications\PackageUpdatedNotification;
use App\Services\Tracker\Contracts\TrackerServiceInterface;
use App\Services\Tracker\Enums\TrackingStatus;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class UpdatePackageDataJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected Package $package)
    {
        $this->onQueue('packages');
    }

    public function handle(TrackerServiceInterface $trackerService): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $trackData = $trackerService->track($this->package->code);

        if ($trackData->lastEventAt->gte($this->package->last_event_at)) {
            $packageUpdateData = [
                'last_event_at' => $trackData->lastEventAt,
            ];

            $newEvents = array_map(
                fn(\App\Services\Tracker\Entities\Event $event) => [
                    'datetime' => $event->datetime,
                    'location' => $event->location,
                    'status' => $event->status,
                    'message' => $event->message,
                    'subStatus' => $event->subStatus
                ],
                array_filter(
                    $trackData->events,
                    fn(\App\Services\Tracker\Entities\Event $event) => !in_array(
                        $event->datetime,
                        $this->package->events->pluck('datetime')->toArray()
                    )
                )
            );

            $this->package->events()->createMany($newEvents);

            if ($this->package->refresh()->events()->latest('datetime')->first()->status ===
                TrackingStatus::DELIVERED->name) {
                $packageUpdateData['status'] = 'completed';
            }

            $this->package->update($packageUpdateData);

            Notification::send($this->package->users, new PackageUpdatedNotification($this->package));
        }
    }
}
