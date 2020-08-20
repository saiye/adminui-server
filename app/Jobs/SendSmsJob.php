<?php

namespace App\Jobs;

use App\Service\SmsApi\AliYunSms;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $noteSms = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($NoteSms)
    {
        $this->noteSms= $NoteSms;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(AliYunSms $api)
    {
        //$api->send($this->noteSms->type,$this->noteSms->area_code,$this->noteSms->phone,$this->noteSms->msg);
    }
}
