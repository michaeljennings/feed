<?php

namespace Michaeljennings\Feed\Contracts;

use Michaeljennings\Feed\Feed;

interface PullFeed
{
    /**
     * Get all of the unread notifications for the provided notifiable members.
     *
     * @param array|Notifiable $notifiable
     * @return mixed
     */
    public function pull($notifiable);

    /**
     * Get all of the read notifications for the provided notifiable members.
     *
     * @param array|Notifiable $notifiable
     * @return mixed
     */
    public function pullRead($notifiable);

    /**
     * Set the amount to limit the feed by.
     *
     * @param int|string $limit
     * @return $this
     */
    public function limit($limit);

    /**
     * Set the amount to offset the feed by.
     *
     * @param int|string $offset
     * @return $this
     */
    public function offset($offset);

    /**
     * Set the amount to paginate the feed by.
     *
     * @param int|string $perPage
     * @return $this
     */
    public function paginate($perPage);

    /**
     * Add a filter to be called on the query results.
     *
     * @param callable $filter
     * @return $this
     */
    public function filter(callable $filter);

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