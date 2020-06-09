<?php

namespace App\Providers\App\Listeners;

use App\Providers\App\Events\ActionLogEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ActionLogEventListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  ActionLogEvent  $event
     * @return void
     */
    public function handle(ActionLogEvent $event)
    {
        //
    }
}
