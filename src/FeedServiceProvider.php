<?php

namespace Michaeljennings\Feed;

use Illuminate\Support\ServiceProvider;
use Michaeljennings\Feed\Store\Manager;

class FeedServiceProvider extends ServiceProvider
{
    /**
     * @inheritdoc
     */
    public function boot()
    {
        $this->publishes([__DIR__ . '/../migrations/' => database_path('migrations')], 'migrations');
        $this->publishes([__DIR__ . '/../config/' => config_path()], 'config');

        $this->mergeConfigFrom(__DIR__ . '/../config//feed.php', 'feed');
    }

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->app->bind('Michaeljennings\Feed\Contracts\Store', function($app) {
            return (new Manager($app))->driver();
        });

        $this->app->bind('feed', function($app) {
            return new Feed($app['Michaeljennings\Feed\Contracts\Store']);
        });

        $this->app->alias('Michaeljennings\Feed\Contracts\Store', 'feed.store');
        $this->app->alias('feed', 'Michaeljennings\Feed\Contracts\PullFeed');
        $this->app->alias('feed', 'Michaeljennings\Feed\Contracts\PushFeed');
    }
}