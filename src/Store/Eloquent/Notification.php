<?php

namespace Michaeljennings\Feed\Store\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Michaeljennings\Feed\Contracts\Notification as NotificationContract;
use Michaeljennings\Feed\Contracts\Store;

class Notification extends Model implements NotificationContract, Store
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