# Feed

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

// Get 10 notifications for the team
$notifications = $feed->limit(10)->pull($team);

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
    - [Marking Notification as Read](#marking-notification-as-read)
    - [Marking Notification as Unread](#marking-notification-as-unread)

## Installation

This package requires at least laravel 5.

To install through composer include the package in your `composer.json`.

    "michaeljennings/feed": "0.1.*"

Run `composer install` or `composer update` to download the dependencies or you can run `composer require michaeljennings/feed`.

Once installed add the service provider to the providers array in `config/app.php`.

```php
'providers' => [

  Michaeljennings\Feed\FeedServiceProvider::class

];
```

The package comes with migrations to setup the default database structure. We recommend using the migration and adding any columns as needed.

To publish the migrations run `php artisan vendor:publish`.

## Using the Feed

Once installed you can access the feed in multiple ways.

Firstly you can dependency inject it from the IOC container by either the push or pull feed interfaces. Both interfaces will return the same instance, it's just to make your code more readable.

```php
public function __construct(
    Michaeljennings\Feed\Contracts\PullFeed $pullFeed,   
    Michaeljennings\Feed\Contracts\PushFeed $pushFeed,   
) {
    $this->pullFeed = $pullFeed;
    $this->pushFeed = $pushFeed;
}
```

Or you there is a helper method.

```php
$feed = feed();
```

Or if you want to use a facade you can register it in the aliases array in `config/app.php`.

```php
'aliases' => [
    'Feed' => Michaeljennings\Feed\Facades\Feed::class
]
```

## Setting Up Notifiable Models

To set up a notifiable model you simply need to implement the notifiable interface and then use the notifiable trait.

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

It is also possible to set up groups of notifiable models.

An example of when this would be useful is having a team of users. This will allow us to push a notification to all the members of that group.

To set up a notifiable group simply implement the notifiable group interface. This will add a method called `getGroup` which requires you to return the members you would like to be notified.

In the example below we have a team model which implements the group interface. It has a `users` relationship which returns all of the users belonging to the group. The in the `getGroup` method we simply return the users.

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

Below is a list of all of the currently available methods. If you think of anything you want to add please feel free to create an issue or pull request.

### Push

The push method allows you to push a notification to a notifiable model, or multiple notifiable models.

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

You can then listen for this and then broadcast it, send an email etc.

You simply need to register the listeners in the event service provider.

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

You can also limit and offset the notifications but using the `limit` and `offset` methods respectively.

```php
$feed->limit(10)->pull($user);
$feed->limit(10)->offset(1)->pull($user);
```

### Pull Read

To get all of the read notifications for a member, use the `pullRead` method.

```php
$feed->pullRead($user);
```

You can also limit and offset the read notifications but using the `limit` and `offset` methods respectively.

```php
$feed->limit(10)->pullRead($user);
$feed->limit(10)->offset(1)->pullRead($user);
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
