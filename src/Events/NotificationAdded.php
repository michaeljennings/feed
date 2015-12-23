<?php

namespace Michaeljennings\Feed\Events;

use Michaeljennings\Feed\Contracts\Notifiable;
use Michaeljennings\Feed\Contracts\Notification;

class NotificationAdded
{
    /**
     * The notifiable member.
     *
     * @var Notifiable
     */
    protected $notifiable;

    /**
     * The notification that was added.
     *
     * @var Notification
     */
    protected $notification;

    public function __construct(Notification $notification, Notifiable $notifiable)
    {
        $this->notifiable = $notifiable;
        $this->notification = $notification;
    }

    /**
     * Return the notifiable member.
     *
     * @return Notifiable
     */
    public function getNotifiable()
    {
        return $this->notifiable;
    }

    /**
     * Return the notification that was added.
     *
     * @return Notification
     */
    public function getNotification()
    {
        return $this->notification;
    }
}