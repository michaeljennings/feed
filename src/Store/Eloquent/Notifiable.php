<?php

namespace Michaeljennings\Feed\Store\Eloquent;

trait Notifiable
{
    /**
     * The member's notifications.
     *
     * @return mixed
     */
    public function notifications()
    {
        return $this->morphMany('Michaeljennings\Feed\Store\Eloquent\Notification', 'notifiable');
    }
}