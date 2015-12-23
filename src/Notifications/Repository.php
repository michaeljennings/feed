<?php

namespace Michaeljennings\Feed\Notifications;

use Michaeljennings\Feed\Contracts\Repository as RepositoryContracts;

class Repository implements RepositoryContracts
{
    /**
     * Create a new notification.
     *
     * @param array $notification
     * @return \Michaeljennings\Feed\Contracts\Notification
     */
    public function newNotification(array $notification)
    {
        return new Notification($notification);
    }

    /**
     * @param array $types
     * @param array $ids
     * @param null  $limit
     * @param null  $offset
     * @return mixed
     */
    public function getNotifications(array $types, array $ids, $limit = null, $offset = null)
    {
        $query = Notification::whereIn('notifiable_type', $types)
                             ->whereIn('notifiable_id', $ids)
                             ->where('read', false);

        if ($limit) {
            $query->limit($limit);
        }

        if ($offset) {
            $query->offset($offset);
        }

        return $query->get();
    }
}