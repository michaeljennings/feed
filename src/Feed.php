<?php

namespace Michaeljennings\Feed;

use Illuminate\Support\Collection;
use Michaeljennings\Feed\Contracts\Notifiable;
use Michaeljennings\Feed\Contracts\NotifiableGroup;
use Michaeljennings\Feed\Contracts\Notification;
use Michaeljennings\Feed\Contracts\PullFeed;
use Michaeljennings\Feed\Contracts\PushFeed;
use Michaeljennings\Feed\Contracts\Repository;
use Michaeljennings\Feed\Events\NotificationAdded;
use Michaeljennings\Feed\Events\NotificationRead;
use Michaeljennings\Feed\Events\NotificationUnread;
use Michaeljennings\Feed\Exceptions\NotNotifiableException;
use Traversable;

class Feed implements PushFeed, PullFeed
{
    /**
     * The amount to limit the feed by.
     *
     * @var int|string|null
     */
    protected $limit = null;

    /**
     * The amount to offset the feed by.
     *
     * @var int|string|null
     */
    protected $offset = null;

    /**
     * The notification repository implementation.
     *
     * @var Repository
     */
    protected $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
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
            if ( ! $toBeNotified instanceof Notifiable && !$tobeNotified instanceof NotifiableGroup) {
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

        return $this->repository->getNotifications($types, $ids, $this->limit, $this->offset);
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

        return $this->repository->getReadNotifications($types, $ids, $this->limit, $this->offset);
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
            if ( ! $toBeNotified instanceof Notifiable) {
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
        $this->limit = $limit;

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
        $this->offset = $offset;

        return $this;
    }

    /**
     * Mark the provided notification as read.
     *
     * @param int|Notification $notification
     * @return mixed
     */
    public function read($notification)
    {
        $this->repository->read($notification);

        event(new NotificationRead($notification));

        return $notification;
    }

    /**
     * Alias for the read function.
     *
     * @param int|Notification $notification
     * @return mixed
     */
    public function markAsRead($notification)
    {
        return $this->read($notification);
    }

    /**
     * Mark the provided notification as unread.
     *
     * @param int|Notification $notification
     * @return mixed
     */
    public function unread($notification)
    {
        $this->repository->unread($notification);

        event(new NotificationUnread($notification));

        return $notification;
    }

    /**
     * Alias for the unread function.
     *
     * @param int|Notification $notification
     * @return mixed
     */
    public function markAsUnread($notification)
    {
        return $this->unread($notification);
    }

    /**
     * Create a new notification.
     *
     * @param array $notification
     * @return Notifications\Notification
     */
    protected function makeNotification(array $notification)
    {
        return $this->repository->newNotification($notification);
    }
}
