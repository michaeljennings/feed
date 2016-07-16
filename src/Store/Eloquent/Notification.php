<?php

namespace Michaeljennings\Feed\Store\Eloquent;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Michaeljennings\Feed\Contracts\Notification as NotificationContract;
use Michaeljennings\Feed\Contracts\Store;

class Notification extends Model implements NotificationContract, Store
{
    /**
     * The database table to be used by the model.
     *
     * @var string
     */
    protected $table = 'notifications';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id', 'notifiable_type', 'notifiable_id', 'created_at', 'updated_at'];

    /**
     * The amount to limit the queries by.
     *
     * @var null|int
     */
    protected $limit = null;

    /**
     * The amount to offset the queries by.
     *
     * @var null|int
     */
    protected $offset = null;

    /**
     * The member to be notified.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function notifiable()
    {
        return $this->morphTo();
    }

    /**
     * Get all of the unread notifications where their notifiable type
     * is in the array of types, and their notifiable id is in the
     * array of ids.
     *
     * @param array $types
     * @param array $ids
     * @return NotificationContract[]
     */
    public function getNotifications(array $types, array $ids)
    {
        $query = $this->whereIn('notifiable_type', $types)
                      ->whereIn('notifiable_id', $ids)
                      ->where('read', false);

        if ($this->limit) {
            $query->limit($this->limit);
        }

        if ($this->offset) {
            $query->offset($this->offset);
        }

        return $query->get();
    }

    /**
     * Get all of the read notifications where their notifiable type
     * is in the array of types, and their notifiable id is in the
     * array of ids.
     *
     * @param array $types
     * @param array $ids
     * @return NotificationContract[]
     */
    public function getReadNotifications(array $types, array $ids)
    {
        $query = $this->whereIn('notifiable_type', $types)
                      ->whereIn('notifiable_id', $ids)
                      ->where('read', true);

        if ($this->limit) {
            $query->limit($this->limit);
        }

        if ($this->offset) {
            $query->offset($this->offset);
        }

        return $query->get();
    }

    /**
     * Add a limit to the query.
     *
     * @param int $limit
     * @return Store
     */
    public function limit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Add an offset to the query.
     *
     * @param int $offset
     * @return Store
     */
    public function offset($offset)
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * Mark the provided notification as read.
     *
     * @param Notification|int|array $notifications
     * @return bool
     */
    public function markAsRead($notifications)
    {
        if ( ! is_array($notifications)) {
            $notifications = func_get_args();
        }

        $ids = $this->getIds($notifications);

        return $this->whereIn($this->getKeyName(), $ids)->update(['read' => true, 'read_at' => new Carbon()]);
    }

    /**
     * Mark the provided notification as unread.
     *
     * @param Notification|int|array $notifications
     * @return bool
     */
    public function markAsUnread($notifications)
    {
        if ( ! is_array($notifications)) {
            $notifications = func_get_args();
        }

        $ids = $this->getIds($notifications);

        return $this->whereIn($this->getKeyName(), $ids)->update(['read' => false, 'read_at' => null]);
    }

    /**
     * Get the primary of all of the notifications.
     *
     * @param array $notifications
     * @return array
     */
    public function getIds($notifications)
    {
        return array_map(function($notification) {
            if ($notification instanceof NotificationContract) {
                return $notification->getKey();
            } else {
                return $notification;
            }
        }, $notifications);
    }
}