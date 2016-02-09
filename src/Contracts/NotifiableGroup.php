<?php

namespace Michaeljennings\Feed\Contracts;

interface NotifiableGroup
{
    /**
     * Get all of the notifiable members for the group.
     *
     * @return Notifiable[]
     */
    public function getGroup();
}