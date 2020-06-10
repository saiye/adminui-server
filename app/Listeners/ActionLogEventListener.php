<?php

namespace App\Listeners;

use App\Events\ActionLogEvent;
use App\Models\ActionLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

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
        $params=$event->req->except('_token','password');
        $log=ActionLog::create([
            'date'=>date('Y-m-d H:i:s'),
            'guard'=>$event->guard,
            'ip'=>$event->req->ip(),
            'uri'=> $path=$event->req->path(),
            'params'=>json_encode($params),
            'user_id'=>$event->user->id,
            'user'=>$event->user->user_name,
            'http_type' => $event->req->server ( 'REQUEST_METHOD', '' ),
        ]);
       $log->save();
    }
}
