<?php

namespace Michaeljennings\Feed;

use Illuminate\Support\ServiceProvider;

class FeedServiceProvider extends ServiceProvider
{
    /**
     * @inheritdoc
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../migrations/' => database_path('migrations')
        ], 'migrations');
    }

    /**
     * @inheritdoc
     */
    public function register()
    {


        $this->app->bind('michaeljennings.feed.repository', 'Michaeljennings\Feed\Notifications\Repository');

        $this->app->bind('michaeljennings.feed', function($app) {
            return new Feed($app['michaeljennings.feed.repository']);
        });

        $this->app->alias('michaeljennings.feed.repository', 'Michaeljennings\Feed\Contracts\Repository');
        $this->app->alias('michaeljennings.feed', 'Michaeljennings\Feed\Contracts\PullFeed');
        $this->app->alias('michaeljennings.feed', 'Michaeljennings\Feed\Contracts\PushFeed');
    }
}