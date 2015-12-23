<?php

namespace Michaeljennings\Feed\Notifications;

trait Notifiable
{
    /**
     * The member's notifications.
     *
     * @return mixed
     */
    public function notifications()
    {
        return $this->morphMany('Michaeljennings\Feed\Notifications\Notification', 'notifiable');
    }
}