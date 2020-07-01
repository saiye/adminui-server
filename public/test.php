<?php
$url='http://192.168.1.78:9501/client/queryDeviceRoomData';
$data=[
    'deviceShortId'=>'1025',
];


function post_curl($url, $data, array $header=[])
{
    $postData=is_array($data)?http_build_query($data):$data;

    $ch = curl_init();
    if(substr($url,0,5)=='https'){
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, 2 );
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    if($header){
        $headData=[];
        foreach ($header as $k=>$d){
            array_push($headData,$k.': '.$d);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headData);
    }
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    if($error=curl_error($ch)){
        return '{}';
    }
    curl_close($ch);
    return $response;

}


$res=post_curl($url,$data);

var_dump($res);

