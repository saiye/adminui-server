<?php
/**
 * Created by 2020/10/23 0023 08:02
 * User: yuansai chen
 */

namespace App\Service\WebSocket;

use Illuminate\Http\Request;
use Swoole\Websocket\Frame;

interface HandlerContract
{
    /**
     * "onOpen" listener.
     *
     * @param int $fd
     * @param \Illuminate\Http\Request $request
     */
    public function onOpen($fd, Request $request);

    /**
     * "onMessage" listener.
     *  only triggered when event handler not found
     *
     * @param \Swoole\Websocket\Frame $frame
     */
    public function onMessage(Frame $frame);

    /**
     * "onClose" listener.
     *
     * @param int $fd
     * @param int $reactorId
     */
    public function onClose($fd, $reactorId);
}
