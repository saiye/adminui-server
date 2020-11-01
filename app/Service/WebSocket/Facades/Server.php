<?php
/**
 * Created by 2020/10/23 0023 08:09
 * User: yuansai chen
 */

namespace App\Service\WebSocket\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Server
 *
 * @mixin \Swoole\Http\Server
 */
class Server extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'swoole';
    }
}
