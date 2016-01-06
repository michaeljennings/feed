<?php

namespace Michaeljennings\Feed\Contracts;

interface Repository
{
    /**
     * Create a new notification.
     *
     * @param array $notification
     * @return \Michaeljennings\Feed\Contracts\Notification
     */
    public function newNotification(array $notification);

    /**
     * Get all of the unread notifications for the notifiable members.
     *
     * @param array $types
     * @param array $ids
     * @param null  $limit
     * @param null  $offset
     * @return mixed
     */
    public function getNotifications(array $types, array $ids, $limit = null, $offset = null);

    /**
     * Get all of the read notifications for the notifiable members.
     *
     * @param array $types
     * @param array $ids
     * @param null  $limit
     * @param null  $offset
     * @return mixed
     */
    public function getReadNotifications(array $types, array $ids, $limit = null, $offset = null);

    /**
     * Mark the notification as read.
     *
     * @param Notification $notification
     * @return mixed
     */
    public function read(Notification $notification);

    /**
     * Mark the notification as unread.
     *
     * @param Notification $notification
     * @return mixed
     */
    public function unread(Notification $notification);
}