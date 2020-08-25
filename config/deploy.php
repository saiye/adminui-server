<?php
return [
    'check' => [
        '0' => '未审核',
        '1' => '审核通过',
        '2' => '审核不通过'
    ],
    'lock' => [
        1 => '正常',
        2 => '禁封',
    ],
    'judge' => [
        '1' => '是',
        '2' => '否',
    ],
    'time_type' => [
       1=> [
            'value' => 1,
            'name' => '分钟',
        ],2=>[
            'value' => 2,
            'name' => '小时',
        ],
        3=>[
            'value' => 3,
            'name' => '天',
        ],
    ],
    'price_type' => [
        1=> [
            'value' => 1,
            'name' => '人民币',
        ],
        2=>[
            'value' => 2,
            'name' => '美元',
        ],
        3=>[
            'value' => 3,
            'name' => '新加坡元',
        ],
    ],
    'is_use'=>[
        1=>'游戏中',
        2=>'空闲',
    ],
    'store_tag'=>[
        1=>'大包间',
        2=>'有景观位',
        3=>'可停车',
        4=>'有充电宝',
    ],
    'sms_type'=>[
        1=>'注册',
        2=>'找回密码',
    ],
    'aliSmsKey'=>[
        'accessKeyId'=>env('AliSmsAccessKeyId',''),
        'accessSecret'=>env('AliSmsAccessSecret',''),
        'signName'=>env('AliSmsSignName',''),
    ],
    'vonSmsKey'=>[
        'accessKeyId'=>env('VonSmsAccessKeyId',''),
        'accessSecret'=>env('VonSmsAccessSecret',''),
        'from'=>env('AliSmsSignName',''),
    ],
];
