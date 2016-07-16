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
     * Mark the provided notification(s) as read.
     *
     * @param int|Notification|array $notifications
     * @return mixed
     */
    public function markAsRead($notifications);

    /**
     * Alias for the mark as read function.
     *
     * @param int|Notification|array $notifications
     * @return mixed
     */
    public function read($notifications);

    /**
     * Mark the provided notification(s) as unread.
     *
     * @param int|Notification|array $notifications
     * @return mixed
     */
    public function markAsUnread($notifications);

    /**
     * Alias for the mark as unread function.
     *
     * @param int|Notification|array $notifications
     * @return mixed
     */
    public function unread($notifications);
}
