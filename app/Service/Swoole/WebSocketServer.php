<?php

namespace App\Service\Swoole;


class WebSocketServer implements Server
{

    public $server = null;

    public function __construct()
    {
        $this->server=new \Swoole\Websocket\Server("127.0.0.1", 9502);
        $this->server->on('open', [$this, 'open']);
        $this->server->on('message', [$this, 'message']);
        $this->server->on('close', [$this, 'close']);
       // $this->server->on('start', [$this, 'start']);
    }
    public function open($server, $req)
    {
        echo "connection open: {$req->fd}\n";
    }

    public function message($server, $frame)
    {
        echo "received message: {$frame->data}\n";
        $server->push($frame->fd, json_encode(["hello", "world"]));

    }

    public function close($server, $fd)
    {
        echo "connection close: {$fd}\n";
    }
    public function start()
    {
        $this->server->start();
    }


}
