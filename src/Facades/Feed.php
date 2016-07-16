<?php

namespace Michaeljennings\Feed\Facades;

use Illuminate\Support\Facades\Facade;

class Feed extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'feed';
    }
}