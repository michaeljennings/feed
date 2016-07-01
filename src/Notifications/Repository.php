<?php

namespace Michaeljennings\Feed\Notifications;

use Carbon\Carbon;
use Michaeljennings\Feed\Contracts\Notification as NotificationContract;
use Michaeljennings\Feed\Contracts\Repository as RepositoryContract;

class Repository implements RepositoryContract
{
    /**
     * Find a notification by it's id
     *
     * @param int $id
     * @return \Michaeljennings\Feed\Contracts\Notification
     */
    public function find($id)
    {
        return Notification::where('id', $id)->first();
    }

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
     * Get all of the unread notifications for the notifiable members.
     *
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

    /**
     * Get all of the read notifications for the notifiable members.
     *
     * @param array $types
     * @param array $ids
     * @param null  $limit
     * @param null  $offset
     * @return mixed
     */
    public function getReadNotifications(array $types, array $ids, $limit = null, $offset = null)
    {
        $query = Notification::whereIn('notifiable_type', $types)
                             ->whereIn('notifiable_id', $ids)
                             ->where('read', true);

        if ($limit) {
            $query->limit($limit);
        }

        if ($offset) {
            $query->offset($offset);
        }

        return $query->get();
    }

    /**
     * Mark the notification as read.
     *
     * @param int|NotificationContract $notification
     * @return mixed
     */
    public function read($notification)
    {
        if(! $notification instanceof NotificationContract) {
            $notification = $this->find($notification);
        }

        $notification->read = true;
        $notification->read_at = new Carbon();

        $notification->save();

        return $notification;
    }

    /**
     * Mark the notification as unread.
     *
     * @param int|NotificationContract $notification
     * @return mixed
     */
    public function unread($notification)
    {
        if(! $notification instanceof NotificationContract) {
            $notification = $this->find($notification);
        }

        $notification->read = false;
        $notification->read_at = null;

        $notification->save();

        return $notification;
    }
}
