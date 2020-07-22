<?php

namespace App\Jobs;

use App\Constants\CacheKey;
use App\Constants\ErrorCode;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CallBackGameLogin implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 最大失败次数
     * @var int
     */
    public $tries = 5;
    /**
     * @var int
     * 最大异常数
     */
    public $maxExceptions = 3;

    /**
     *该任务允许运行的最大时长
     */
    public $timeout = 10;


    public $url = null;
    public $post = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($url, $post)
    {
        $this->url = $url;
        $this->post = $post;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = new Client([
            'timeout' => 3,
        ]);
       // Log::info($this->post);
        $response = $client->post($this->url, [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'verify' => false,
            'json' => $this->post
        ]);
        if ($response->getStatusCode() == 200) {
            Log::info('call game login success!' . $this->url);
        } else {
            Log::info('call game login error! url:' . $this->url);
            Log::info($this->post);
        }
        if(Cache::get(CacheKey::API_LOG_RECORD)){
            Log::info($this->post);
        }
    }
}
