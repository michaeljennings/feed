<?php

if ( ! function_exists('feed')) {

    /**
     * Get the notifications for the notifiable members.
     *
     * If no notifiable members are passed then return the feed instance.
     *
     * @param null $notifiable
     * @return \Illuminate\Foundation\Application|mixed
     */
    function feed($notifiable = null)
    {
        $feed = app('michaeljennings.feed');

        if ($notifiable) {
            return $feed->pull($notifiable);
        }

        return $feed;
    }

}