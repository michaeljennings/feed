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
     * @param bool  $read
     * @return Notification[]
     */
    public function getNotifications(array $types, array $ids, $read = false);

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
     * Set the amount to paginate the results by.
     *
     * @param int $perPage
     * @return $this
     */
    public function paginateResults($perPage);

    /**
     * Add a filter to be called at run time.
     *
     * @param callable $filter
     * @return $this
     */
    public function filter(callable $filter);

    /**
     * Mark the provided notification as read.
     *
     * @param array|NotificationContract[] $notifications
     * @return NotificationContract[]
     */
    public function markAsRead(array $notifications);

    /**
     * Mark the provided notification as unread.
     *
     * @param array|NotificationContract[] $notifications
     * @return NotificationContract[]
     */
    public function markAsUnread(array $notifications);

    /**
     * Order the results by the latest notification.
     *
     * @param string $column
     * @return $this
     */
    public function latest($column = 'created_at');

    /**
     * Order the results by the oldest notification.
     *
     * @param string $column
     * @return $this
     */
    public function oldest($column = 'created_at');
}