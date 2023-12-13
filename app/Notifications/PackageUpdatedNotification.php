<?php

namespace App\Notifications;

use App\Models\Package;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;

use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class PackageUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Package $package)
    {
    }

    /**
     * Determine which queues should be used for each notification channel.
     *
     * @return array<string, string>
     */
    public function viaQueues(): array
    {
        return [
            FcmChannel::class => 'firebase',
        ];
    }

    public function via($notifiable): array
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable): FcmMessage
    {
        return (new FcmMessage(
            notification: new FcmNotification(
                title: __('Package updated'),
                body: __('Your package status has been updated, check it out!'),
            )))
            ->data(['package' => $this->package->code, 'lastEventAt' => $this->package->last_event_at])
            ->custom([
                'android' => [
                    'notification' => [
                        'color' => '#0A0A0A',
                    ],
                    'fcm_options' => [
                        'analytics_label' => 'analytics_android',
                    ],
                ],
                'apns' => [
                    'fcm_options' => [
                        'analytics_label' => 'analytics_ios',
                    ],
                ],
            ]);
    }
}
