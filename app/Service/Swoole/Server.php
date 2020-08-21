<?php
namespace App\Service\Swoole;


interface Server
{

    public function open($server, $req);

    public function message($server, $frame);

    public function close($server, $fd);

    public function start();

}
