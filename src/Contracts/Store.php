<?php

namespace Michaeljennings\Feed\Contracts;

interface Store
{
    /**
     * Get all of the unread notifications where their notifiable type
     * is in the array of types, and their notifiable id is in the
     * array of ids.
     *
     * @param array $types
     * @param array $ids
     * @return Notification[]
     */
    public function getNotifications(array $types, array $ids);

    /**
     * Get all of the read notifications where their notifiable type
     * is in the array of types, and their notifiable id is in the
     * array of ids.
     *
     * @param array $types
     * @param array $ids
     * @return Notification[]
     */
    public function getReadNotifications(array $types, array $ids);

    /**
     * Add a limit to the query.
     *
     * @param int $limit
     * @return Store
     */
    public function limit($limit);

    /**
     * Add an offset to the query.
     *
     * @param int $offset
     * @return Store
     */
    public function offset($offset);

    /**
     * Mark the provided notification as read.
     *
     * @param Notification|int|array $notifications
     * @return Notification[]
     */
    public function markAsRead($notifications);

    /**
     * Mark the provided notification as unread.
     *
     * @param Notification|int|array $notifications
     * @return Notification[]
     */
    public function markAsUnread($notifications);

    /**
     * Set the amount to paginate the results by.
     *
     * @param int $perPage
     * @return $this
     */
    public function paginateResults($perPage);
}