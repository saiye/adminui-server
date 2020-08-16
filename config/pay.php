<?php
return [
    'pay_type' => [
        1 => '微信',
        2 => '支付宝',
        3 => '银行卡',
        4 => '乐马支付',
        5 => '钱包',
    ],
    'bind_type' => [
        0 => '消费',
        1 => '充值',
    ],
    'pay_state' => [
        0 => '未支付',
        1 => '支付成功',
    ],
    'status'=>[
        0=>'未支付',
        1=>'已支付',
        2=>'用户取消',
        3=>'订单完成',
    ],
    'check_status'=>[
        0=>'待审核',
        1=>'处理中',
        2=>'已驳回',
        3=>'已取消',
        4=>'已完成',
    ],
    'key' => [
        'wx' => [
            'appId' => env('WX_APP_ID',''),
            'mchId' =>env('WX_MCH_ID',''),
            'appSecret' => env('WX_PAY_KEY',''),
            'sslCertPath'=>env('WX_PAY_CERT_PATH',''),
            'sslKeyPath'=>env('WX_PAY_KEY_PATH',''),
        ],
    ],
    'selectConf' => [
        [
            'id' => 0,
            'name' => '无',
        ], [
            'id' => 1,
            'name' => '商品名称',
        ], [
            'id' => 2,
            'name' => '订单号',
        ], [
            'id' => 3,
            'name' => '用户名',
        ], [
            'id' => 4,
            'name' => '用户ID',
        ]
    ]
];
