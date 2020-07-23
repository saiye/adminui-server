<?php

/*$arr=[
    'age'=>'10',
    'name'=>'buffer',
];


function scene_decode($code)
{
    $res = explode('&', $code);
    $post = [];
    foreach ($res as $val) {
        $v = explode('=', $val);
        if (count($v) == 2)
            $post[$v[0]]=$v[1];
    }
    return $post;
}

    function scene_encode($data)
    {
        $str='';
        foreach ($data as $k=>$v){
            $str.=$k.'@'.$v;
        }
        $str=implode('|',$data);
        return $str;
    }

    $str=scene_encode($arr);

    echo $str;*/


echo "str=str.split('?')[1].substring(6)";

