<?php


if (!function_exists('msubstr')) {
    /**
     * 字符串截取
     * @param           $str
     * @param int $start
     * @param           $length
     * @param string $charset
     * @param bool|true $suffix
     * @return string
     */
    function msubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = true)
    {
        if (function_exists("mb_substr")) {
            $slice = mb_substr($str, $start, $length, $charset);
        } elseif (function_exists('iconv_substr')) {
            $slice = iconv_substr($str, $start, $length, $charset);
            if (false === $slice) {
                $slice = '';
            }
        } else {
            $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
            $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
            $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
            $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
            preg_match_all($re[$charset], $str, $match);
            $slice = join("", array_slice($match[0], $start, $length));
        }
        return $suffix ? $slice . '***' : $slice;

    }
}


if (!function_exists("get_description")) {
    /**
     * 根据文章ID截取描述
     * @param $id
     * @param int $word
     * @return string
     */
    function get_description($content, $word = 210)
    {
        if (empty($content)) {
            return '...';
        }
        $description = msubstr(strip_tags($content), 0, $word);
        return $description;
    }

}

if (!function_exists("zh_date")) {
    /**
     * 根据文章ID截取描述
     * @param $id
     * @param int $word
     * @return string
     */
    function zh_date()
    {
        $h = date('G');
        if ($h < 12) {
            return '上午';
        } elseif ($h < 18) {
            return '下午';
        }
        return '晚上';
    }

}
if (!function_exists("get_between_date")) {

    function get_between_date($start_date, $end_date, $format = 'Y-m-d')
    {
        $start_time = is_numeric($start_date) ? $start_date : strtotime($start_date);
        $end_time = is_numeric($end_date) ? $end_date : strtotime($end_date);
        if ($start_time > $end_time) {
            $tem = $end_time;
            $end_time = $start_time;
            $start_time = $tem;
        }
        if ($format == 'Y-m-d' or $format == 'Ymd') {
            $new_date = date('Y-m-d H:i:s', $start_time);
        } else {
            $new_date = date('Y-m', $start_time);
        }

        $dates = array();
        while (($date = strtotime($new_date)) <= $end_time) {
            $dates [] = date($format, $date);
            if ($format == 'Y-m') {
                $new_date = date('Y-m', $date) . ' +1 month ';
            } else {
                $new_date = date('Y-m-d H:i:s', $date) . ' +1 day ';
            }

        }
        return $dates;
    }

}

if (!function_exists("diffBetweenTwoDays")) {
    function diffBetweenTwoDays($day1, $day2)
    {
        $second1 = strtotime($day1);
        $second2 = strtotime($day2);

        if ($second1 < $second2) {
            $tmp = $second2;
            $second2 = $second1;
            $second1 = $tmp;
        }
        return ($second1 - $second2) / 86400;
    }
}
if (!function_exists("get_address")) {
    function get_address($ip)
    {
        static $qqwry = null;
        if (class_exists('qqwry')) {
            if (is_null($qqwry))
                $qqwry = new qqwry(storage_path() . '/qqwry.dat');
            return array_map(function ($address) {
                return @iconv('GB2312', 'UTF-8', $address);
            }, $qqwry->q($ip));
        }
        return '';
    }
}
if (!function_exists("isChinaIp")) {
    function isChinaIp($ip)
    {
        $in = substr($ip, 0, 3);
        if (in_array($in, ['192', '127'])) {
            return 0;
        }
        $url = 'http://ip.taobao.com/service/getIpInfo.php?ip=' . $ip;
        $str = file_get_contents($url);
        if ($str) {
            $res = json_decode($str, true);
            if (isset($res['code']) and $res['code'] == 0) {
                if ($res['data']['country'] == '中国') {
                    return 1;
                }
            }
        }
        return 0;
    }

}
if (!function_exists("ipAddress")) {
    function ipAddress($ip)
    {
        $ip = get_address($ip);
        if (is_array($ip) and count($ip) == 2) {
            return $ip[0] . $ip[1];
        }
        return $ip;
    }
}

if (!function_exists("makeSign")) {
    function makeSign($data, $key)
    {
        unset($data['sign']);
        ksort($data);
        $da = [];
        foreach ($data as $k => $v) {
            if ($v)
                array_push($da, $k . '=' . $v);
        }
        $str = implode('&', $da) . '&key=' . $key;
        return md5($str);
    }
}

if (!function_exists("checkSign")) {
    function checkSign($data, $sign, $key)
    {
        $tmpSign = makeSign($data, $key);
        return $tmpSign === $sign;
    }
}

if (!function_exists("checkDev")) {
    function checkDev($value)
    {
        return in_array($value, ['ios', 'android']);
    }
}


if (!function_exists("filterNull")) {
    function filterNull($data)
    {
        $ok = array_filter($data, function ($k) {
            return !is_null($k);
        });
        foreach ($ok as $k => &$val) {
            if (is_array($val)) {
                $ok[$k] = filterNull($val);
            }
        }
        return $ok;
    }
}

if (!function_exists("get_https_curl")) {
    function get_https_curl($url, $data = '', array $header)
    {
        if (is_array($data)) {
            $data = http_build_query($data);
        }
        $result = array();
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_TIMEOUT, 10); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        if ($header) {
            $headData = [];
            foreach ($header as $k => $d) {
                array_push($headData, $k . ': ' . $d);
            }
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headData);
        }
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        $result ['data'] = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            $result ['error'] = curl_error($curl); // 捕获异常
        }
        curl_close($curl);
        return $result;
    }
}
if (!function_exists("post_curl")) {
    function post_curl($url, $data, array $header = [])
    {
        $postData = is_array($data) ? http_build_query($data) : $data;

        $ch = curl_init();
        if (substr($url, 0, 5) == 'https') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($header) {
            $headData = [];
            foreach ($header as $k => $d) {
                array_push($headData, $k . ': ' . $d);
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headData);
        }
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        if ($error = curl_error($ch)) {
            return '{}';
        }
        curl_close($ch);
        return $response;

    }
}

if (!function_exists("scene_decode")) {
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
}
if (!function_exists("scene_encode")) {
    function scene_encode($data)
    {
        return http_build_query($data);
    }
}
if (!function_exists("index_by")) {
    function index_by($data,$key)
    {
        $post=[];
        if(is_object($data)){
            foreach ($data as $v){
                $post[$v->$key]=$v;
            }
        }elseif (is_array($data)){
            foreach ($data as $v){
                $post[$v[$key]]=$v;
            }
        }

        return $post;
    }
}
if (!function_exists("get_distance")) {
    /**
     * 根据经纬度算距离，返回结果单位是公里，先纬度，后经度
     * @param $lat1
     * @param $lng1
     * @param $lat2
     * @param $lng2
     * @return float|int
     */
    function get_distance($lat1, $lng1, $lat2, $lng2)
    {
        $EARTH_RADIUS = 6378.137;

        $radLat1 =my_rad($lat1);
        $radLat2 = my_rad($lat2);
        $a = $radLat1 - $radLat2;
        $b = my_rad($lng1) - my_rad($lng2);
        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
        $s = $s * $EARTH_RADIUS;
        $s = round($s * 10000) / 10000;
        return $s;
    }
}
if (!function_exists("my_rad")) {
    function my_rad($d)
    {
        return $d * M_PI / 180.0;
    }
}

if (!function_exists("enCode")) {
    function enCode( $key, $value,$cipher='aes-256-ecb')
    {
        if (in_array($cipher, openssl_get_cipher_methods())) {
            $value = openssl_encrypt($value,
                $cipher, $key, OPENSSL_RAW_DATA
            );
        }else{
            $value='-';
        }
        return base64_encode($value);
    }
}
if (!function_exists("deCode")) {
    function deCode($key, $value,$cipher='aes-256-ecb')
    {
        if (in_array($cipher, openssl_get_cipher_methods())) {
            $str= openssl_decrypt(base64_decode($value),$cipher,$key,OPENSSL_RAW_DATA);
        }else{
            $str='';
        }
        return $str;
    }
}




