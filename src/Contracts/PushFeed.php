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

    /**
     * Mark the provided notification as read.
     *
     * @param Notification $notification
     * @return mixed
     */
    public function read(Notification $notification);

    /**
     * Alias for the read function.
     *
     * @param Notification $notification
     * @return mixed
     */
    public function markAsRead(Notification $notification);

    /**
     * Mark the provided notification as unread.
     *
     * @param Notification $notification
     * @return mixed
     */
    public function unread(Notification $notification);

    /**
     * Alias for the unread function.
     *
     * @param Notification $notification
     * @return mixed
     */
    public function markAsUnread(Notification $notification);
}