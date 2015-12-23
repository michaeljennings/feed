<?php

namespace Michaeljennings\Feed\Notifications;

use Illuminate\Database\Eloquent\Model;
use Michaeljennings\Feed\Contracts\Notification as NotificationContract;

class Notification extends Model implements NotificationContract
{
    /**
     * The database table to be used by the model.
     *
     * @var string
     */
    protected $table = 'notifications';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id', 'notifiable_type', 'notifiable_id', 'created_at', 'updated_at'];

    /**
     * The member to be notified.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function notifiable()
    {
        return $this->morphTo();
    }
}