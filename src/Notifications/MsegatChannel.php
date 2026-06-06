<?php

namespace Aghfatehi\Msegat\Notifications;

use Aghfatehi\Msegat\Facades\Msegat;
use Illuminate\Notifications\Notification;

/**
 * Laravel notification channel for sending SMS via Msegat.
 *
 * Expects the notification class to implement a toMsegat() method
 * that returns the message string. The notifiable must define a
 * routeNotificationFor('msegat') method returning the phone number.
 */
class MsegatChannel
{
    /**
     * Send the given notification as an Msegat SMS.
     *
     * @param  object  $notifiable  The notifiable entity.
     * @param  Notification  $notification  The notification instance.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        $message = $notification->toMsegat($notifiable);

        if (is_string($message)) {
            $to = $notifiable->routeNotificationFor('msegat', $notification);

            Msegat::sms()
                ->to($to)
                ->message($message)
                ->send();
        }
    }
}
