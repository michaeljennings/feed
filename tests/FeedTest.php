<?php

namespace Michaeljennings\Feed\Tests;

use Michaeljennings\Feed\Events\NotificationAdded;
use Michaeljennings\Feed\Events\NotificationRead;
use Michaeljennings\Feed\Events\NotificationUnread;

class FeedTest extends DbTestCase
{
    /**
     * @test
     */
    public function it_pushes_a_notification_to_a_notifiable()
    {
        $feed = $this->make();
        $user = new User();

        $this->assertEquals(0, $feed->pull($user)->count());
        $feed->push('This is a test notification', $user);

        $notifications = $feed->pull($user);

        $this->assertEquals(1, $notifications->count());
        $this->assertEquals('This is a test notification', $notifications->first()->body);
    }

    /**
     * @test
     */
    public function it_pushes_a_notification_to_multiple_users_in_an_array()
    {
        $feed = $this->make();
        $user1 = new User();
        $user2 = new User(['id' => 2]);

        $this->assertEquals(0, $feed->pull($user1)->count());
        $this->assertEquals(0, $feed->pull($user2)->count());

        $feed->push('This is a test notification', [$user1, $user2]);

        $notifications = $feed->pull($user1);

        $this->assertEquals(1, $notifications->count());
        $this->assertEquals('This is a test notification', $notifications->first()->body);

        $notifications = $feed->pull($user2);

        $this->assertEquals(1, $notifications->count());
        $this->assertEquals('This is a test notification', $notifications->first()->body);
    }

    /**
     * @test
     */
    public function it_pushes_a_notification_to_multiple_users_in_a_collection()
    {
        $feed = $this->make();
        $user1 = new User();
        $user2 = new User(['id' => 2]);

        $this->assertEquals(0, $feed->pull($user1)->count());
        $this->assertEquals(0, $feed->pull($user2)->count());

        $feed->push('This is a test notification', collect([$user1, $user2]));

        $notifications = $feed->pull($user1);

        $this->assertEquals(1, $notifications->count());
        $this->assertEquals('This is a test notification', $notifications->first()->body);

        $notifications = $feed->pull($user2);

        $this->assertEquals(1, $notifications->count());
        $this->assertEquals('This is a test notification', $notifications->first()->body);
    }

    /**
     * @test
     */
    public function it_pushes_to_a_notifiable_group()
    {
        $feed = $this->make();
        $team = new Team();
        $user = new User();
        $user2 = new User(['id' => 2]);

        $this->assertEquals(0, $feed->pull($team)->count());
        $feed->push('This is a test notification', $team);

        $notifications = $feed->pull($team);

        $this->assertEquals(1, $notifications->count());
        $this->assertEquals('This is a test notification', $notifications->first()->body);

        $notifications = $feed->pull($user);

        $this->assertEquals(1, $notifications->count());
        $this->assertEquals('This is a test notification', $notifications->first()->body);

        $notifications = $feed->pull($user2);

        $this->assertEquals(0, $notifications->count());
    }

    /**
     * @test
     */
    public function it_pushes_a_notification_with_multiple_parameters()
    {
        $feed = $this->make();
        $user = new User();

        $this->assertEquals(0, $feed->pull($user)->count());
        $feed->push([
            'body' => 'This is a test notification',
            'icon' => 'fa fa-alert',
        ], $user);

        $notifications = $feed->pull($user);

        $this->assertEquals(1, $notifications->count());
        $this->assertEquals('This is a test notification', $notifications->first()->body);
        $this->assertEquals('fa fa-alert', $notifications->first()->icon);
    }

    /**
     * @test
     */
    public function it_does_not_require_an_icon()
    {
        $feed = $this->make();
        $user = new User();

        $this->assertEquals(0, $feed->pull($user)->count());
        $feed->push([
            'body' => 'This is a test notification',
        ], $user);

        $notifications = $feed->pull($user);

        $this->assertEquals(1, $notifications->count());
        $this->assertEquals('This is a test notification', $notifications->first()->body);
        $this->assertNull($notifications->first()->icon);
    }

    /**
     * @test
     */
    public function it_pulls_read_notifications()
    {
        $feed = $this->make();
        $user = new User();

        $this->assertEquals(0, $feed->pull($user)->count());
        $feed->push('This notification will not be read', $user);
        $feed->push('This notification will be read', $user);

        $notifications = $feed->pull($user);

        $this->assertEquals(2, $notifications->count());

        $feed->markAsRead($notifications->last());

        $this->assertEquals(1, $feed->pull($user)->count());

        $readNotifications = $feed->pullRead($user);

        $this->assertEquals(1, $readNotifications->count());
        $this->assertEquals('This notification will be read', $readNotifications->first()->body);
    }

    /**
     * @test
     */
    public function it_marks_notification_as_read_using_the_read_alias()
    {
        $feed = $this->make();
        $user = new User();

        $this->assertEquals(0, $feed->pull($user)->count());
        $feed->push('This notification will not be read', $user);
        $feed->push('This notification will be read', $user);

        $notifications = $feed->pull($user);

        $this->assertEquals(2, $notifications->count());

        $feed->read($notifications->last());

        $this->assertEquals(1, $feed->pull($user)->count());

        $readNotifications = $feed->pullRead($user);

        $this->assertEquals(1, $readNotifications->count());
        $this->assertEquals('This notification will be read', $readNotifications->first()->body);
    }

    /**
     * @test
     */
    public function it_marks_a_notification_as_unread()
    {
        $feed = $this->make();
        $user = new User();

        $this->assertEquals(0, $feed->pull($user)->count());
        $feed->push('This notification will not be read', $user);
        $feed->push('This notification will be read', $user);

        $notifications = $feed->pull($user);

        $this->assertEquals(2, $notifications->count());

        $feed->markAsRead($notifications->last());

        $this->assertEquals(1, $feed->pull($user)->count());

        $readNotifications = $feed->pullRead($user);

        $this->assertEquals(1, $readNotifications->count());
        $this->assertEquals('This notification will be read', $readNotifications->first()->body);

        $feed->markAsUnread($readNotifications->first());

        $notifications = $feed->pull($user);

        $this->assertEquals(2, $notifications->count());
        $this->assertEquals('This notification will be read', $notifications->last()->body);

        $this->assertEquals(0, $feed->pullRead($user)->count());
    }

    /**
     * @test
     */
    public function it_marks_a_notification_as_unread_using_the_alias()
    {
        $feed = $this->make();
        $user = new User();

        $this->assertEquals(0, $feed->pull($user)->count());
        $feed->push('This notification will not be read', $user);
        $feed->push('This notification will be read', $user);

        $notifications = $feed->pull($user);

        $this->assertEquals(2, $notifications->count());

        $feed->read($notifications->last());

        $this->assertEquals(1, $feed->pull($user)->count());

        $readNotifications = $feed->pullRead($user);

        $this->assertEquals(1, $readNotifications->count());
        $this->assertEquals('This notification will be read', $readNotifications->first()->body);

        $feed->unread($readNotifications->first());

        $notifications = $feed->pull($user);

        $this->assertEquals(2, $notifications->count());
        $this->assertEquals('This notification will be read', $notifications->last()->body);

        $this->assertEquals(0, $feed->pullRead($user)->count());
    }

    /**
     * @test
     */
    public function it_marks_a_notification_as_read_using_the_notification_id()
    {
        $feed = $this->make();
        $user = new User();

        $this->assertEquals(0, $feed->pull($user)->count());
        $feed->push('This notification will not be read', $user);
        $feed->push('This notification will be read', $user);

        $notifications = $feed->pull($user);

        $this->assertEquals(2, $notifications->count());

        $feed->markAsRead($notifications->last()->id);

        $this->assertEquals(1, $feed->pull($user)->count());

        $readNotifications = $feed->pullRead($user);

        $this->assertEquals(1, $readNotifications->count());
        $this->assertEquals('This notification will be read', $readNotifications->first()->body);
    }

    /**
     * @test
     */
    public function it_marks_a_notification_as_unread_using_the_notification_id()
    {
        $feed = $this->make();
        $user = new User();

        $this->assertEquals(0, $feed->pull($user)->count());
        $feed->push('This notification will not be read', $user);
        $feed->push('This notification will be read', $user);

        $notifications = $feed->pull($user);

        $this->assertEquals(2, $notifications->count());

        $feed->markAsRead($notifications->last()->id);

        $this->assertEquals(1, $feed->pull($user)->count());

        $readNotifications = $feed->pullRead($user);

        $this->assertEquals(1, $readNotifications->count());
        $this->assertEquals('This notification will be read', $readNotifications->first()->body);

        $feed->markAsUnread($readNotifications->first()->id);

        $notifications = $feed->pull($user);

        $this->assertEquals(2, $notifications->count());
        $this->assertEquals('This notification will be read', $notifications->last()->body);

        $this->assertEquals(0, $feed->pullRead($user)->count());
    }

    /**
     * @test
     */
    public function it_limits_the_amount_of_notifications_returned()
    {
        $feed = $this->make();
        $user = new User();

        $feed->push('Notification 1', $user);
        $feed->push('Notification 2', $user);
        $feed->push('Notification 3', $user);
        $feed->push('Notification 4', $user);

        $notifications = $feed->limit(2)->pull($user);

        $this->assertEquals(2, $notifications->count());
        $this->assertEquals('Notification 1', $notifications->first()->body);
        $this->assertEquals('Notification 2', $notifications->last()->body);
    }

    /**
     * @test
     */
    public function it_offsets_and_limits_the_returned_notifications()
    {
        $feed = $this->make();
        $user = new User();

        $feed->push('Notification 1', $user);
        $feed->push('Notification 2', $user);
        $feed->push('Notification 3', $user);
        $feed->push('Notification 4', $user);

        $notifications = $feed->limit(2)->offset(1)->pull($user);

        $this->assertEquals(2, $notifications->count());
        $this->assertEquals('Notification 2', $notifications->first()->body);
        $this->assertEquals('Notification 3', $notifications->last()->body);
    }

    /**
     * @test
     */
    public function it_limits_the_amount_of_read_notifications_returned()
    {
        $feed = $this->make();
        $user = new User();

        $feed->push('Notification 1', $user);
        $feed->push('Notification 2', $user);
        $feed->push('Notification 3', $user);
        $feed->push('Notification 4', $user);

        $feed->markAsRead(1);
        $feed->markAsRead(2);
        $feed->markAsRead(3);
        $feed->markAsRead(4);

        $notifications = $feed->limit(2)->pullRead($user);

        $this->assertEquals(2, $notifications->count());
        $this->assertEquals('Notification 1', $notifications->first()->body);
        $this->assertEquals('Notification 2', $notifications->last()->body);
    }

    /**
     * @test
     */
    public function it_limits_and_offsets_the_read_notifications_returned()
    {
        $feed = $this->make();
        $user = new User();

        $feed->push('Notification 1', $user);
        $feed->push('Notification 2', $user);
        $feed->push('Notification 3', $user);
        $feed->push('Notification 4', $user);

        $feed->markAsRead(1);
        $feed->markAsRead(2);
        $feed->markAsRead(3);
        $feed->markAsRead(4);

        $notifications = $feed->limit(2)->offset(1)->pullRead($user);

        $this->assertEquals(2, $notifications->count());
        $this->assertEquals('Notification 2', $notifications->first()->body);
        $this->assertEquals('Notification 3', $notifications->last()->body);
    }

    /**
     * @test
     */
    public function it_fires_a_notification_added_event_when_a_notification_is_pushed()
    {
        $this->expectsEvents(NotificationAdded::class);

        $feed = $this->make();
        $user = new User();

        $feed->push('This is a test notification', $user);
    }

    /**
     * @test
     */
    public function it_fires_a_notification_read_event_when_a_notification_is_marked_as_read()
    {
        $this->expectsEvents(NotificationRead::class);

        $feed = $this->make();
        $user = new User();

        $feed->push('This is a test notification', $user);

        $notifications = $feed->pull($user);

        $feed->markAsRead($notifications->first());
    }

    /**
     * @test
     */
    public function it_fires_a_notification_read_event_when_a_notification_is_marked_as_unread()
    {
        $this->expectsEvents(NotificationUnread::class);

        $feed = $this->make();
        $user = new User();

        $feed->push('This is a test notification', $user);

        $notifications = $feed->pull($user);

        $feed->markAsRead($notifications->first());
        $feed->markAsUnread($notifications->first());
    }

    protected function make()
    {
        return feed();
    }
}