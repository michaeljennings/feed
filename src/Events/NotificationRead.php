<?php

namespace Michaeljennings\Feed\Events;

use Michaeljennings\Feed\Contracts\Notification;

class NotificationRead
{
    /**
     * The notification that was read.
     *
     * @var Notification
     */
    protected $notification;

    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Get the read notification.
     *
     * @return Notification
     */
    public function getNotification()
    {
        return $this->notification;
    }
}