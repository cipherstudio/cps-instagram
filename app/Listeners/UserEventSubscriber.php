<?php

namespace App\Listeners;

class UserEventSubscriber
{
    /**
     * @var \App\InstagramSync $sync
     */
    private $sync;

    public function __construct(\App\InstagramSync $sync)
    {
        $this->sync = $sync;
    }

    /**
     * Handle user login events.
     */
    public function onUserLogin($event) 
    {
        $this->sync->clearAccessTokenUrl();
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'Illuminate\Auth\Events\Login',
            'App\Listeners\UserEventSubscriber@onUserLogin'
        );
    }

}