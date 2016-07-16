<?php

namespace Michaeljennings\Feed\Store;

use Illuminate\Support\Manager as BaseManager;
use Michaeljennings\Feed\Store\Eloquent\Notification;

class Manager extends BaseManager
{
    /**
     * Create the eloquent driver.
     *
     * @return Notification
     */
    public function createEloquentDriver()
    {
        return new Notification();
    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['config']['feed']['driver'];
    }
}