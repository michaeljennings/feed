# Feed [![Build Status](https://travis-ci.org/michaeljennings/feed.svg?branch=master)](https://travis-ci.org/michaeljennings/feed) [![Coverage Status](https://coveralls.io/repos/github/michaeljennings/feed/badge.svg?branch=master)](https://coveralls.io/github/michaeljennings/feed?branch=master)

A basic notification feed for laravel 5+.

Below is some example code of all of the basic methods for the package.

```php
$user = User::find(1);
$team = Team::find(1);

// Push notification to a user
$feed->push('This is a new notification', $user);

// Push notification to a user, and a team of users
$feed->push('This is a new notification', [$user, $team]);

// Push notification to a user with multiple parameters
$feed->push([
    'icon' => 'icon-alert',
    'title' => 'Something Broke!',
    'body' => 'Something super important broke'
], $user);

// Get all of the notifications for a user
$notifications = $feed->pull($user);

// Get 10 notifications for the user
$notifications = $feed->limit(10)->pull($user);

// Mark a notification as read
$feed->markAsRead($notification);
```

## Navigation

- [Installation](#installation)
- [Using the Feed](#using-the-feed)
- [Setting Up Notifiable Models](#setting-up-notifiable-models)
    - [Notifiable Groups](#notifiable-groups)
- [Available Methods](#available-methods)
    - [Push](#push)
    - [Pull](#pull)
        - [Pull Read](#pull-read)
        - [Limiting Results](#limiting-results)
        - [Offsetting Results](#offsetting-results)
        - [Paginate Results](#paginate-results)
        - [Filtering Results](#filtering-results)
        - [Get the Latest Results](#get-the-latest-results)
        - [Get the Oldest Results](#get-the-oldest-results)
        - [Putting it All Together](#putting-it-all-together)
    - [Marking Notification as Read](#marking-notification-as-read)
    - [Marking Notification as Unread](#marking-notification-as-unread)

## Installation

This package requires at least laravel 5.

To install through composer include the package in your `composer.json`.

    "michaeljennings/feed": "0.2.*"

Run `composer install` or `composer update` to download the dependencies, or you can run `composer require michaeljennings/feed`.

Once installed add the service provider to the providers array in `config/app.php`.

```php
'providers' => [

  Michaeljennings\Feed\FeedServiceProvider::class

];
```

To publish the migrations and config files run `php artisan vendor:publish`.

## Configuration

The package comes with a default migration to create the database structure for the package. By default this table allows for the notifications to have a body, and an icon. If need more data for your notification such as a title, we recommend adding the columns to the default migration.

The package comes with `feed.php` config file. This allows you to customise the database driver you are using with the package. At present only eloquent is supported, but we are working on a laravel db driver currently.
 
You may require another driver, i.e. you're using a data store not supported by laravel. If this is the case you can add a driver to the system verify simply as shown below.

```php
// Here we grab the driver manager and then extend it to a new 'foo' driver.
// Ideally this would be done within a service provider. 
app('feed.manager')->extend('foo', function($app) {
    return new Foo();
});

// The Foo driver needs to implement the Store interface so it has all of 
// the necessary methods.
class Foo implements Michaeljennings\Feed\Contracts\Store
{
    //
}

// The in the config file set the driver to our new foo driver.
return [
    'driver' => 'foo'
]
```

For more information on adding drivers look at the [laravel documentation on extending the framework](https://laravel.com/docs/5.0/extending).

## Using the Feed

Once installed you can access the feed in multiple ways.

Firstly you can dependency inject it from the IOC container by either the push or pull feed interfaces. Both interfaces will return the same instance, it's just to make your code more readable.

```php
public function __construct(
    Michaeljennings\Feed\Contracts\PullFeed $pullFeed,
    Michaeljennings\Feed\Contracts\PushFeed $pushFeed
) {
    $this->pullFeed = $pullFeed;
    $this->pushFeed = $pushFeed;
}
```

Or you there is a `feed` helper method.

```php
$feed = feed();
```

Or if you want to use the facade you can register it in the aliases array in `config/app.php`.

```php
'aliases' => [
    'Feed' => Michaeljennings\Feed\Facades\Feed::class
]
```

## Setting Up Notifiable Models

To set up a notifiable model you just need to implement the notifiable interface, and then use the notifiable trait in your model.

This will set up the required relationships.

```php
use Michaeljennings\Feed\Contracts\Notifiable as NotifiableContract;
use Michaeljennings\Feed\Notifications\Notifiable;

class User extends Model implements NotifiableContract
{
    use Notifiable;
}
```

### Notifiable Groups

It is also possible to set up groups of notifiable models, an example of when this would be useful is having a team of users. This will allow us to push a notification to all the members of that group.

To set up a notifiable group you need implement the notifiable group interface on the group model. This will add a method called `getGroup` which requires you to return the members you would like to be notified.

In the example below we have a team model which implements the group interface. It has a `users` relationship which returns all of the users belonging to the team. Then in the `getGroup` method we simply return the users.

```php
use Michaeljennings\Feed\Contracts\NotifiableGroup;

class Team extends Model implements NotifiableGroup
{
    public function users()
    {
        return $this->hasMany('App\User');
    }

    public function getGroup()
    {
        return $this->users;
    }
}
```

## Available Methods

Below is a list of all of the currently available notification methods. If you think of anything you want to add please feel free to create an issue, or a pull request.

### Push

The push method allows you to push a notification to a notifiable model, multiple notifiable models, or a notifiable group.

When pushing to a notifiable group each member of the group will get their own notification, it will not share one notification for all of the members.

```php
$feed->push('My awesome notification', $user);
$feed->push('My awesome notification', [$user, $team]);
$feed->push([
    'title' => 'New Notification',
    'body' => 'My awesome notification'
], $user);
```

When the notification is pushed a `NotificationAdded` event will be fired. 

You can then listen for this and then broadcast the notification, send an email etc.

You just need to register the listeners in the event service provider.

```php
protected $listen = [
    'Michaeljennings\Feed\Events\NotificationAdded' => [
        'App\Listeners\BroadcastNotification',
        'App\Listeners\EmailNotification',
    ],
];
```

### Pull

The pull method gets all of the unread notifications for the notifiable models you pass it.

```php
$feed->pull($user);
```

#### Pull Read

To get all of the read notifications for a member, use the `pullRead` method.

```php
$feed->pullRead($user);
```

#### Limiting Results

To limit the amount of notifications returned when pulling, chain the `limit` method.

```php
$feed->limit(10)->pull($user);
$feed->limit(10)->pullRead($user);
```

#### Offsetting Results

To offset the results when pulling, chain the `offset` method. This can be useful for infinte scrollers.

```php
$feed->offset(10)->pull($user);
$feed->offset(10)->pullRead($user);
```

#### Paginate Results

If you want to paginate the results and let laravel handle the limiting and offsetting for you, chain the `paginate` method.

```php
$feed->paginate(10)->pull($user);
$feed->paginate(10)->pullRead($user);
```

#### Filtering Results

From time to time you may wish to run additional queries on the notification results, to do this chain the filter method. 

The filter method requires a closure which is passed an instance of the query builder. In the example below we're only getting results that have an alert icon.
 
```php
$feed->filter(function($query) {
    $query->where('icon', 'icon-alert');
})->pull($user);

$feed->filter(function($query) {
    $query->where('icon', 'icon-alert');
})->pullRead($user);
```

#### Get the Latest Results

To order the results by the latest notification added, chain the `latest` method. By default the notifications are ordered by the latest notification added.

```php
$feed->latest()->pull($user);
$feed->latest()->pullRead($user);
```

#### Get the Oldest Results

To order the results by the oldest notification added, chain the `oldest` method. By default the notifications are ordered by the latest notification added.

```php
$feed->oldest()->pull($user);
$feed->oldest()->pullRead($user);
```

#### Putting it All Together

All of these methods can be chained together, this should allow you to get the notifications in any way you require.

```php
// Limit and offset the results
$feed->limit(10)->offset(10)->pull($user);


// Get all of the oldest read notifications, that have an alert icon.
$feed->filter(function($query) {
    $query->where('icon', 'icon-alert');
})->oldest()->pullRead($user);
```

### Marking Notification as Read

To mark a notification as read you can either use the `markAsRead` method, or it is aliased to `read` if you prefer.
 
```php
$feed->markAsRead($notification);
$feed->read($notification);
```

When the notification is read marked as read a `NotificationRead` event will be fired. 

You can then listen for this and then broadcast it etc.

```php
protected $listen = [
    'Michaeljennings\Feed\Events\NotificationRead' => [
        'App\Listeners\BroadcastReadNotification',
    ],
];
```

### Marking Notification as Unread

To mark a notification as unread you can either use the `markAsUnread` method, or it is aliased to `unread` if you prefer.
 
```php
$feed->markAsUnread($notification);
$feed->unread($notification);
```

When the notification is read marked as unread a `NotificationUnread` event will be fired. 

You can then listen for this and then broadcast it etc.

```php
protected $listen = [
    'Michaeljennings\Feed\Events\NotificationUnread' => [
        'App\Listeners\BroadcastUnreadNotification',
    ],
];
```
