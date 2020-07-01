<?php

declare(strict_types=1);

namespace App\Constants;

class ErrorCode
{
    const SERVER_ERROR = 500;
    const DEVICE_NOT_BINDING = -1; // 设备未绑定房间
    const ACCOUNT_NOT_EXIST = -2; // 账号不存在
    const ACCOUNT_VALID_FAILURE = -3; // 账号校验失败
    const  ACCOUNT_NO_PREVILEGE = -4; // 该账号没有权限登录当前设备
    const  ACCOUNT_LOCK = -5; // 该账号已经锁定
    const  CONNECTION_TIMEOUT = -6; // 连接超时
    const  VALID_FAILURE = -7; // 接口参数不全，或类型错误
    const  FAIL_LOGIN_CURRENT_DEVICE = -8; // 不能登录当前设备
    const  CHANNEL_NONENTITY = -9; // 渠道不存在
    const  THREE_FAIL = -10; //第三方接口失败
    const  ACCOUNT_NOT_LOGIN = -11; //用户未登录
    const  THREE_ACCOUNT_NOT_LOGIN = -12; //第三方用户未登录，auth2 code验证失败
    const  CREATE_ACCOUNT_ERROR = -13; //创建用户失败
    const  CREATE_ERCODE_ERROR = -14; //二维码生成失败
    const  SUCCESS = 0; // 成功
}
