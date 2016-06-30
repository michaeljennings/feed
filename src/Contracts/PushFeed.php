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
     * @param int|Notification $notification
     * @return mixed
     */
    public function read($notification);

    /**
     * Alias for the read function.
     *
     * @param int|Notification $notification
     * @return mixed
     */
    public function markAsRead($notification);

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
