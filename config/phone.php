<?php

return [
    'aliSmsKey'=>[
        'accessKeyId'=>env('AliSmsAccessKeyId',''),
        'accessSecret'=>env('AliSmsAccessSecret',''),
        'signName'=>env('AliSmsSignName',''),
    ],
    'vonSmsKey'=>[
        'accessKeyId'=>env('VonSmsAccessKeyId',''),
        'accessSecret'=>env('VonSmsAccessSecret',''),
        'from'=>env('VonSmsForm','app'),
    ],
    'status'=>[
        '0'=>'未发送',
        '1'=>'成功',
        '2'=>'失败',
    ],
    'route' => [
        '86' => [
            'name' => '中国',
            'pattern' => '/^1[345789]\d{9}$/',
        ],
        '852' => [
            'name' => '中国香港',
            'pattern' => '/^[569]\d{3}\-?\d{4}$/',
        ],
        '886' => [
            'name' => '中国台湾',
            'pattern' => '/^9\d{8}$/',
        ],
        '1' => [
            'name' => '美国',
            'pattern' => '/^[2-9]\d{2}[2-9](?!11)\d{6}$/',
        ],
        '855' => [
            'name' => '柬埔寨',
            'pattern' => '/^[0-9]*$/',
        ],
        '853' => [
            'name' => '澳门',
            'pattern' => '/^[0-9]*$/',
        ],
        '65' => [
            'name' => '新加坡',
            'pattern' => '/^[0-9]*$/',
        ],
        '60' => [
            'name' => '马来西亚',
            'pattern' => '/^[0-9]*$/',
        ],
        '66' => [
            'name' => '泰国',
            'pattern' => '/^[0-9]*$/',
        ],
        '856' => [
            'name' => '老挝',
            'pattern' => '/^[0-9]*$/',
        ],
        '95' => [
            'name' => '缅甸',
            'pattern' => '/^[0-9]*$/',
        ],
        '84' => [
            'name' => '越南',
            'pattern' => '/^[0-9]*$/',
        ],
        '81' => [
            'name' => '日本',
            'pattern' => '/^[0-9]*$/',
        ],
        '82' => [
            'name' => '韩国',
            'pattern' => '/^[0-9]*$/',
        ],
        '263' => [
            'name' => '津巴布韦',
            'pattern' => '/^[0-9]*$/',
        ],
        '260' => [
            'name' => '赞比亚',
            'pattern' => '/^[0-9]*$/',
        ],
        '967' => [
            'name' => '也门',
            'pattern' => '/^[0-9]*$/',
        ],
        '44' => [
            'name' => '英国',
            'pattern' => '/^[0-9]*$/',
        ],
        '971' => [
            'name' => '阿拉伯联合酋长国',
            'pattern' => '/^[0-9]*$/',
        ],
        '380' => [
            'name' => '乌克兰',
            'pattern' => '/^[0-9]*$/',
        ],
        '256' => [
            'name' => '乌干达',
            'pattern' => '/^[0-9]*$/',
        ],
        '90' => [
            'name' => '土耳其',
            'pattern' => '/^[0-9]*$/',
        ],
        '27' => [
            'name' => '南非',
            'pattern' => '/^[0-9]*$/',
        ],
        '7' => [
            'name' => '俄罗斯',
            'pattern' => '/^[0-9]*$/',
        ],
        '351' => [
            'name' => '葡萄牙',
            'pattern' => '/^[0-9]*$/',
        ],
        '48' => [
            'name' => '波兰',
            'pattern' => '/^[0-9]*$/',
        ],
        '63' => [
            'name' => '菲律宾',
            'pattern' => '/^[0-9]*$/',
        ],
        '52' => [
            'name' => '墨西哥',
            'pattern' => '/^[0-9]*$/',
        ],
        '673' => [
            'name' => '文莱',
            'pattern' => '/^[0-9]*$/',
        ],
        '93' => [
            'name' => '阿富汗',
            'pattern' => '/^[0-9]*$/',
        ],
        '61' => [
            'name' => '澳大利亚',
            'pattern' => '/^[0-9]*$/',
        ],
        '43' => [
            'name' => '奥地利',
            'pattern' => '/^[0-9]*$/',
        ],
        '55' => [
            'name' => '巴西',
            'pattern' => '/^[0-9]*$/',
        ],
    ],
];
