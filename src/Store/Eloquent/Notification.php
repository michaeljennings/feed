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
     * The amount to paginate the queries by.
     *
     * @var null|int
     */
    protected $paginate = null;

    /**
     * An array of closures to be run when
     *
     * @var callable[]
     */
    protected $filters = [];

    /**
     * The column results will be ordered by.
     *
     * @var null|int
     */
    protected $orderBy = 'created_at';

    /**
     * The direction the columns will be ordered in.
     *
     * @var null|int
     */
    protected $orderByDirection = 'desc';

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
     * @param bool  $read
     * @return \Michaeljennings\Feed\Contracts\Notification[]
     */
    public function getNotifications(array $types, array $ids, $read = false)
    {
        $query = $this->whereIn('notifiable_type', $types)
                      ->whereIn('notifiable_id', $ids)
                      ->where('read', $read)
                      ->orderBy($this->orderBy, $this->orderByDirection);

        if ($this->limit) {
            $query->limit($this->limit);
        }

        if ($this->offset) {
            $query->offset($this->offset);
        }

        foreach($this->filters as $filter) {
            $filter($query);
        }

        if ($this->paginate) {
            return $query->paginate($this->paginate);
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
     * Set the amount to paginate the results by.
     *
     * @param int $perPage
     * @return $this
     */
    public function paginateResults($perPage)
    {
        $this->paginate = $perPage;

        return $this;
    }

    /**
     * Add a filter to be called at run time.
     *
     * @param callable $filter
     * @return $this
     */
    public function filter(callable $filter)
    {
        $this->filters[] = $filter;

        return $this;
    }

    /**
     * Order the results by the latest notification.
     *
     * @param string $column
     * @return $this
     */
    public function latest($column = 'created_at')
    {
        $this->orderBy = $column;
        $this->orderByDirection = 'desc';

        return $this;
    }

    /**
     * Order the results by the oldest notification.
     *
     * @param string $column
     * @return $this
     */
    public function oldest($column = 'created_at')
    {
        $this->orderBy = $column;
        $this->orderByDirection = 'asc';

        return $this;
    }

    /**
     * Mark the provided notification as read.
     *
     * @param NotificationContract|int|array $notifications
     * @return NotificationContract[]
     */
    public function markAsRead($notifications)
    {
        if ( ! is_array($notifications)) {
            $notifications = func_get_args();
        }

        $ids = $this->getIds($notifications);

        $this->whereIn($this->getKeyName(), $ids)->update(['read' => true, 'read_at' => new Carbon()]);

        return $this->whereIn($this->getKeyName(), $ids)->get()->all();
    }

    /**
     * Mark the provided notification as unread.
     *
     * @param NotificationContract|int|array $notifications
     * @return NotificationContract[]
     */
    public function markAsUnread($notifications)
    {
        if ( ! is_array($notifications)) {
            $notifications = func_get_args();
        }

        $ids = $this->getIds($notifications);

        $this->whereIn($this->getKeyName(), $ids)->update(['read' => false, 'read_at' => null]);

        return $this->whereIn($this->getKeyName(), $ids)->get()->all();
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