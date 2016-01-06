<?php

namespace Michaeljennings\Feed\Tests;

class FeedTest extends DbTestCase
{
    /**
     * @test
     */
    public function it_pushes_a_notification_to_a_user()
    {
        $user = new User();
        $feed = $this->app['michaeljennings.feed'];

        $this->assertNull($feed->push('test notification', $user));

        $this->assertEquals(1, $user->notifications->count());
    }

    /**
     * @test
     */
    public function it_pulls_notifications_for_a_user()
    {
        $user = new User();
        $feed = $this->app['michaeljennings.feed'];

        $feed->push('test notification', $user);

        $notifications = $feed->pull($user);

        $this->assertEquals(1, $notifications->count());
        $this->assertInstanceOf('Illuminate\Support\Collection', $notifications);

        $notification = $notifications->first();

        $this->assertInstanceOf('Michaeljennings\Feed\Contracts\Notification', $notification);
        $this->assertEquals('test notification', $notification->body);
    }

    /**
     * @test
     */
    public function it_limits_the_amount_of_notifications_returned()
    {
        $user = new User();
        $feed = $this->app['michaeljennings.feed'];

        $feed->push('test notification', $user);
        $feed->push('test notification', $user);

        $notifications = $feed->limit(1)->pull($user);

        $this->assertEquals(1, $notifications->count());
    }

    /**
     * @test
     */
    public function it_offsets_the_returned_notifications()
    {
        $user = new User();
        $feed = $this->app['michaeljennings.feed'];

        $feed->push('test notification', $user);
        $feed->push('foo notification', $user);

        $notifications = $feed->limit(1)->offset(1)->pull($user);

        $this->assertEquals('foo notification', $notifications->first()->body);
    }

    /**
     * @test
     */
    public function it_marks_a_notification_as_read()
    {
        $user = new User();
        $feed = $this->app['michaeljennings.feed'];

        $feed->push('test notification', $user);

        $notifications = $feed->pull($user);

        $this->assertEquals(1, $notifications->count());

        $feed->read($notifications->first());

        $notifications = $feed->pull($user);

        $this->assertEquals(0, $notifications->count());
    }

    /**
     * @test
     */
    public function it_marks_a_notification_as_read_using_alias()
    {
        $user = new User();
        $feed = $this->app['michaeljennings.feed'];

        $feed->push('test notification', $user);

        $notifications = $feed->pull($user);

        $this->assertEquals(1, $notifications->count());

        $feed->markAsRead($notifications->first());

        $notifications = $feed->pull($user);

        $this->assertEquals(0, $notifications->count());
    }

    /**
     * @test
     */
    public function it_marks_a_read_notification_as_unread()
    {
        $user = new User();
        $feed = $this->app['michaeljennings.feed'];

        $feed->push('test notification', $user);

        $notifications = $feed->pull($user);
        $feed->read($notifications->first());

        $notifications = $feed->pullRead($user);

        $this->assertEquals(1, $notifications->count());

        $feed->unread($notifications->first());

        $notifications = $feed->pullRead($user);

        $this->assertEquals(0, $notifications->count());
    }

    /**
     * @test
     */
    public function it_marks_a_read_notification_as_unread_using_alias()
    {
        $user = new User();
        $feed = $this->app['michaeljennings.feed'];

        $feed->push('test notification', $user);

        $notifications = $feed->pull($user);
        $feed->read($notifications->first());

        $notifications = $feed->pullRead($user);

        $this->assertEquals(1, $notifications->count());

        $feed->markAsUnread($notifications->first());

        $notifications = $feed->pullRead($user);

        $this->assertEquals(0, $notifications->count());
    }
}