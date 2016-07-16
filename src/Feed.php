<?php

namespace Michaeljennings\Feed;

use Illuminate\Support\Collection;
use Michaeljennings\Feed\Contracts\Notifiable;
use Michaeljennings\Feed\Contracts\NotifiableGroup;
use Michaeljennings\Feed\Contracts\Notification;
use Michaeljennings\Feed\Contracts\PullFeed;
use Michaeljennings\Feed\Contracts\PushFeed;
use Michaeljennings\Feed\Contracts\Store;
use Michaeljennings\Feed\Events\NotificationAdded;
use Michaeljennings\Feed\Events\NotificationRead;
use Michaeljennings\Feed\Events\NotificationUnread;
use Michaeljennings\Feed\Exceptions\NotNotifiableException;
use Traversable;

class Feed implements PushFeed, PullFeed
{
    /**
     * The notification store implementation.
     *
     * @var Store
     */
    protected $store;

    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    /**
     * Push the provided notification to the notifiable members.
     *
     * @param string|array            $notification
     * @param Notifiable[]|Notifiable $notifiable
     * @throws NotNotifiableException
     */
    public function push($notification, $notifiable)
    {
        if ( ! is_array($notifiable) && ! $notifiable instanceof Traversable) {
            $notifiable = [$notifiable];
        }

        foreach ($notifiable as $toBeNotified) {
            if ( ! $toBeNotified instanceof Notifiable && !$toBeNotified instanceof NotifiableGroup) {
                throw new NotNotifiableException("The notifiable members must implement the notifiable interface.");
            }
        }

        $notification = is_string($notification) ? ['body' => $notification] : $notification;

        foreach ($notifiable as $toBeNotified) {
            $this->pushNotification($notification, $toBeNotified);
        }
    }

    /**
     * Push a notification to a member.
     *
     * @param string|array $notification
     * @param Notifiable   $notifiable
     */
    protected function pushNotification($notification, $notifiable)
    {
        if ($notifiable instanceof NotifiableGroup) {
            foreach ($notifiable->getGroup() as $toBeNotified) {
                $this->push($notification, $toBeNotified);
            }
        } else {
            // @todo Change this fucking shite
            $notification = $notifiable->notifications()->create($notification);

            event(new NotificationAdded($notification, $notifiable));
        }
    }

    /**
     * Get all of the unread notifications for the provided notifiable members.
     *
     * @param array|Notifiable $notifiable
     * @return mixed
     * @throws NotNotifiableException
     */
    public function pull($notifiable)
    {
        if ( ! is_array($notifiable)) {
            $notifiable = func_get_args();
        }

        $notifiable = $this->getUsersToPullFor($notifiable);

        $types = array_map(function ($notifiable) {
            return get_class($notifiable);
        }, $notifiable);

        $ids = array_map(function ($notifiable) {
            return $notifiable->getKey();
        }, $notifiable);

        return $this->store->getNotifications($types, $ids);
    }

    /**
     * Get all of the read notifications for the provided notifiable members.
     *
     * @param array|Notifiable $notifiable
     * @return mixed
     */
    public function pullRead($notifiable)
    {
        if ( ! is_array($notifiable)) {
            $notifiable = func_get_args();
        }

        $notifiable = $this->getUsersToPullFor($notifiable);

        $types = array_map(function ($notifiable) {
            return get_class($notifiable);
        }, $notifiable);

        $ids = array_map(function ($notifiable) {
            return $notifiable->getKey();
        }, $notifiable);

        return $this->store->getReadNotifications($types, $ids);
    }

    /**
     * Get the users to pull notifications for from the notifiable members.
     *
     * @param array $notifiable
     * @return array
     * @throws NotNotifiableException
     */
    protected function getUsersToPullFor($notifiable)
    {
        foreach ($notifiable as $key => $toBeNotified) {
            if ( ! $toBeNotified instanceof Notifiable && ! $toBeNotified instanceof NotifiableGroup) {
                throw new NotNotifiableException("The members passed to the pull must implement the notifiable interface");
            }

            if ($toBeNotified instanceof NotifiableGroup) {
                $group = $toBeNotified->getGroup();

                if ($group instanceof Collection) {
                    $group = $group->all();
                }

                $notifiable = array_merge($notifiable, $group);

                unset($notifiable[$key]);
            }
        }

        return $notifiable;
    }

    /**
     * Set the amount to limit the feed by.
     *
     * @param int|string $limit
     * @return $this
     */
    public function limit($limit)
    {
        $this->store->limit($limit);

        return $this;
    }

    /**
     * Set the amount to offset the feed by.
     *
     * @param int|string $offset
     * @return $this
     */
    public function offset($offset)
    {
        $this->store->offset($offset);

        return $this;
    }

    /**
     * Set the amount to paginate the feed by.
     *
     * @param int|string $perPage
     * @return $this
     */
    public function paginate($perPage)
    {
        $this->store->paginateResults($perPage);

        return $this;
    }

    /**
     * Add a filter to be called on the query results.
     *
     * @param callable $filter
     * @return $this
     */
    public function filter(callable $filter)
    {
        $this->store->filter($filter);

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
        $this->store->latest($column);

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
        $this->store->oldest($column);

        return $this;
    }

    /**
     * Mark the provided notification as read.
     *
     * @param int|Notification|array $notifications
     * @return mixed
     */
    public function markAsRead($notifications)
    {
        if ( ! is_array($notifications)) {
            $notifications = func_get_args();
        }

        $notifications = $this->store->markAsRead($notifications);

        foreach ($notifications as $notification) {
            event(new NotificationRead($notification));
        }

        return count($notifications) == 1 ? $notifications[0] : $notifications;
    }

    /**
     * Alias for the mark as read function.
     *
     * @param int|Notification|array $notifications
     * @return mixed
     */
    public function read($notifications)
    {
        return $this->markAsRead($notifications);
    }

    /**
     * Mark the provided notification as unread.
     *
     * @param int|Notification|array $notifications
     * @return mixed
     */
    public function markAsUnread($notifications)
    {
        if ( ! is_array($notifications)) {
            $notifications = func_get_args();
        }

        $notifications = $this->store->markAsUnread($notifications);

        foreach ($notifications as $notification) {
            event(new NotificationUnread($notification));
        }

        return count($notifications) == 1 ? $notifications[0] : $notifications;
    }

    /**
     * Alias for the mark as unread function.
     *
     * @param int|Notification|array $notifications
     * @return mixed
     */
    public function unread($notifications)
    {
        return $this->markAsUnread($notifications);
    }
}
