<?php

$data=[
    'buys'=>[

    ],
    'user_coupon_id'=>0,
];
$goodsIds=[
  /*  [
        'id'=>1,
        'sku'=>[['tag_id'=>12,'sku_id'=>4],['tag_id'=>11,'sku_id'=>2]],
    ],*/
    [
        'id'=>2,
        'sku'=>[['tag_id'=>14,'sku_id'=>9],['tag_id'=>13,'sku_id'=>8]],
    ],
    [
        'id'=>3,
        'sku'=>[['tag_id'=>15,'sku_id'=>12],['tag_id'=>16,'sku_id'=>15]],
    ],
];
$type=1;
$count=10;

foreach ($goodsIds as $item){
    array_push($data['buys'],[
        'goodsId' => $item['id'],
        'ext' => $item['sku'],
        'type' => $type,
        'count' => $count,
    ]);
}

echo json_encode($data['buys']);
