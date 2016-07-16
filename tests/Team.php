<?php

namespace Michaeljennings\Feed\Tests;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Michaeljennings\Feed\Contracts\Notifiable;
use Michaeljennings\Feed\Contracts\NotifiableGroup;

class Team extends Model implements NotifiableGroup
{
    /**
     * Get all of the notifiable members for the group.
     *
     * @return Notifiable[]
     */
    public function getGroup()
    {
        return new Collection([new User()]);
    }
}