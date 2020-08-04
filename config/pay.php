<?php
return [
    'pay_type' => [
        1 => '微信',
        2 => '钱包',
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
    'key' => [
        'wx' => [
            'appId' => '',
            'mchId' => '',
            'key' => '',
            'appSecret' => '',
        ],
    ],
    'selectConf' => [
        [
            'id' => 1,
            'name' => '无',
        ], [
            'id' => 2,
            'name' => '商品名称',
        ], [
            'id' => 3,
            'name' => '订单号',
        ], [
            'id' => 4,
            'name' => '用户名',
        ], [
            'id' => 5,
            'name' => '用户ID',
        ]
    ]
];
