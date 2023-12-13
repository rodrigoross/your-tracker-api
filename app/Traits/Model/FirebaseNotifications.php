<?php

namespace App\Traits\Model;

/**
 * @property array|string $fcm_token
 */
trait FirebaseNotifications
{
    public function initializeFirebaseNotificationsTrait(): void
    {
        $this->fillable[] = 'fcm_token';
    }

    /**
     * Specifies the user's FCM token
     *
     * @return string|array
     */
    public function routeNotificationForFcm(): array|string
    {
        return $this->fcm_token;
    }
}
