<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Driver
    |--------------------------------------------------------------------------
    |
    | Select the database driver you wish to use to retrieve your notifiations.
    |
    | Currently supported: eloquent
    |
    */

    'driver' => 'eloquent',

    /*
    |--------------------------------------------------------------------------
    | Drivers
    |--------------------------------------------------------------------------
    |
    | Set any driver specific configuration options here.
    |
    */

    'drivers' => [
        'eloquent' => [
            'model' => 'Michaeljennings\Feed\Store\Eloquent\Notification',
        ],
    ]

];