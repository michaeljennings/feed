<?php

namespace Michaeljennings\Feed\Contracts;

interface Notification
{
    /**
     * Get the notification's primary key.
     *
     * @return int|string
     */
    public function getKey();
}