<?php
/**
 * Created by 2020/7/5 0005 15:29
 * User: yuansai chen
 */

namespace App\Service\GameApi;


use App\Models\Channel;

interface GameApi
{
    public function __construct(Channel $channel);
    /**
     * 登录回调
     * @return mixed
     */
    public function loginCallBack($data);

    /**
     * 登出回调
     * @return mixed
     */
    public function logicLogout($data);

    /**
     * 查询游戏信息
     * @return mixed
     */
    public function logicQueryGameInfo($data);


}
