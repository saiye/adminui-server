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
    const  DATA_NULL = -16; // 数据不存在
    const  CREATE_ORDER_FAILURE = -17; //订单创建失败
    const  STORE_ORDER_FAILURE = -18; //存在跨店铺商品
    const  ORDER_PARAM_FAILURE = -19; //订单参数错误
    const  ORDER_GOODS_FAILURE = -20; //订单商品入库失败!
    const  ORDER_NOT_FIND = -21; //订单商品入库失败!
    const  GOODS_CLOSE= -22; //商品已下架
    const  GOODS_SELL_OUT= -23; //商品已售罄
    const  GOODS_SKU_EDIT= -24; //商品规格已修改
    const  GOODS_NOT_FIND= -25; //商品不存在
    const  ORDER_IS_PAY= -26; //订单已支付
    const  ORDER_IS_CANCEL= -27; //订单已取消
    const  BALANCE_CANT= -28; //余额不足
    const  SMS_OFTEN= -29; //操作短信太频繁
    const  SMS_NOT_SUPPORT= -30; //不支持该地区短信
    const  LOGOUT_FAILURE_IN_GAMING=-31; //游戏中无法退出
    const  SMS_CODE_FAILURE=-32; //验证码无效
    const  REPEAT_BUILD_PHONE=-33; //请勿重复绑定
    const  REPETITION_CODE=-34; //不能重复扫码登录
    const  GAME_REPLAY_NULL=-35; //游戏复盘数据不存在
    const  QRCODE_NOT_USE=-36; //二维码未使用
    const  SIGN_CHECK_FAIL=-37; //签名错误
    const  USER_EDIT_PWD_FAIL=-38; //密码修改失败
    const  REPEAT_APPLY=-39; //重复报名
    const  APPLY_FAIL=-40; //报名失败
    const  SUCCESS = 0; // 成功
}
