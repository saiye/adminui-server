<?php

namespace App\Jobs;

use App\Models\NoteSms;
use App\Service\SmsApi\AliYunSms;
use App\Service\SmsApi\VonSms;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $noteSms = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(NoteSms $NoteSms)
    {
        $this->noteSms = $NoteSms;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $env=Config::get('app.env');
        if(in_array($env,['production'])){
            /*    if ($this->noteSms->area_code == 86) {
                    $api = new AliYunSms();
                } else {
                    $api = new VonSms();
                }*/
                $api = new AliYunSms();
                list($status, $res) = $api->send($this->noteSms->type, $this->noteSms->area_code, $this->noteSms->phone, $this->noteSms->msg, $this->noteSms->action);
                if ($status) {
                    $this->noteSms->res = $res;
                    $this->noteSms->status = $status ? 1 : 2;
                    $this->noteSms->save();
                }
        }else{
            Log::info('app.env:'.$env.'短信不下发!');
        }
    }

}
