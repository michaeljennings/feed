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
    public function markAsRead($notification);

    /**
     * Alias for the mark as read function.
     *
     * @param int|Notification $notification
     * @return mixed
     */
    public function read($notification);

    /**
     * Mark the provided notification as unread.
     *
     * @param int|Notification $notification
     * @return mixed
     */
    public function markAsUnread($notification);

    /**
     * Alias for the mark as unread function.
     *
     * @param int|Notification $notification
     * @return mixed
     */
    public function unread($notification);
}
