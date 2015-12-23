<?php

namespace Michaeljennings\Feed\Contracts;

interface PushFeed
{
    /**
     * Push the provided notification to the notifiable members.
     *
     * @param string|array     $notification
     * @param array|Notifiable $notifiable
     */
    public function push($notification, $notifiable);
}