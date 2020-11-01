<?php
/**
 * Created by 2020/8/21 0021 22:35
 * User: yuansai chen
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Service\WebSocket\WebSocketServer;
use Illuminate\Support\Facades\Log;

class WebSocket extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'socket';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'web socket';


    public  $server=null;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
       // $this->server=$server;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
       // $this->server->start();
    }
}
