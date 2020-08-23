<?php

namespace App\Service\Pay;

use App\Constants\ErrorCode;
use Config;
use Request;
use Log;
use Illuminate\Support\Str;

/**
 * 微信支付
 * @author chenyuansai
 *
 */
final class WeiXinPayApi extends PayApi
{

    //是否打印log
    const DEBUG = true;
    //是否为沙盒测试,上线为否
    const IS_SAND_BOX = false;

    public function init()
    {
        $this->config = Config::get('pay.key.wx');
    }

    /*
     * 微信支付回调
     */
    function callBack($call)
    {
        $xml = file_get_contents('php://input');

        if (!$xml) {
            return self::createXml([
                'return_code' => 'FAIL',
                'return_msg' => '验证失败'
            ]);
        }
        $http_params = $this->fromXml($xml);
        $appid = $this->config['appId'];
        $mch_id = $this->config['mchId'];
        //验证回调参数
        $tmp_sign = $this->MakeSign($http_params);
        if (isset($http_params['sign']) and $http_params['sign'] == $tmp_sign) {
            //是否支付ok?
            if (isset($http_params['return_code']) and $http_params['return_code'] == 'SUCCESS') {
                if (isset($http_params['result_code']) and $http_params['result_code'] == 'SUCCESS') {
                    if ($http_params['appid'] !== $appid) {
                        return self::createXml([
                            'return_code' => 'FAIL',
                            'return_msg' => 'appid不正确'
                        ]);
                    }
                    if ($http_params['mch_id'] !== $mch_id) {
                        return self::createXml([
                            'return_code' => 'FAIL',
                            'return_msg' => 'mch_id不正确'
                        ]);
                    }
                    //有用代金券的情况下，应结订单金额作为回调金额
                    $calPrice = $http_params['settlement_total_fee'] ?? $http_params['total_fee'];
                    $flag = $call([
                        'prepay_id' => $http_params['transaction_id'],
                        'total_price' => $http_params['total_fee'] / 100,
                        'actual_payment' => $calPrice / 100,
                        'order_sn' => $http_params['out_trade_no'],
                        'time_end' => $http_params['time_end'],
                        'pay_type' => 1,
                    ]);
                    if ($flag) {
                        return self::createXml([
                            'return_code' => 'SUCCESS',
                            'return_msg' => 'OK'
                        ]);
                    } else {
                        Log::info('wxPayCallL:异常订单');
                        Log::info($xml);
                        return self::createXml([
                            'return_code' => 'SUCCESS',
                            'return_msg' => '验证ok,金额不正确,归类异常订单!'
                        ]);
                    }
                }
            }
        }
        Log::info('wxPayCallL:失败订单');
        Log::info($xml);
        return self::createXml([
            'return_code' => 'FAIL',
            'return_msg' => '验证失败'
        ]);
    }

    /*
     * 微信统一下单
     */
    function createOrder($order)
    {
        $price = $order['due_price'] * 100;
        $appid = $this->config['appId'];
        $mch_id = $this->config['mchId'];
        $time = date('YmdHis');
        $http_query = array(
            'appid' => $appid,
            'mch_id' => $mch_id,
            'nonce_str' => Str::random(32),
            'sign_type' => 'MD5',
            'body' => $order->store->store_name,
            'out_trade_no' => $order['order_sn'],
            'fee_type' => 'CNY',
            'total_fee' => $price,
            'spbill_create_ip' => $this->request->ip(),
            'time_start' => $time,
            'time_expire' => date('YmdHis', strtotime('+3hour')),
            'notify_url' => route('wx-callWx'),
            'trade_type' => $order['trade_type'] ?? 'JSAPI',
            'openid' => $order['openid'] ?? '',
            'scene_info' => json_encode([
                'name' => $order->store->store_name,
                'address' => $order->store->address,
            ]),
        );
        $http_query['sign'] = $this->MakeSign($http_query);
        $xml = self::createXml($http_query);
        if (self::DEBUG) {
            Log::info('createOrderXML:');
            Log::info($xml);
        }
        $repose_xml = $this->postXmlCurl($xml, self::getBaseUrl('/pay/unifiedorder'));
        $repose_arr = $this->fromXml($repose_xml);

        //通讯成功
        if (isset($repose_arr['return_code']) and $repose_arr['return_code'] == 'SUCCESS') {
            //业务成功
            if (isset($repose_arr['result_code']) and $repose_arr['result_code'] == 'SUCCESS') {
                //预充值订单
                $sendData = [
                    'appId' => $appid,
                    'timeStamp' => time(),
                    'nonceStr' => Str::random(32),
                    'package' => 'prepay_id=' . $repose_arr['prepay_id'],
                    'signType' => 'MD5',
                ];
                $sendData['paySign'] = $this->MakeSign($sendData);
                return [
                    'code' => ErrorCode::SUCCESS,
                    'errorMessage' => '微信下单成功',
                    'data' => $sendData,

                ];
            }
            Log::info('原生微信下单失败-业务失败' . $order->order_sn);
            Log::info($repose_arr);
            return [
                'code' => ErrorCode::THREE_FAIL,
                'errorMessage' => '微信下单失败-业务失败,err_code:' . $repose_arr['err_code'] ?? ($repose_arr['err_code_des'] ?? ''),
            ];
        }
        Log::info('原生微信下单失败' . $order->order_sn);
        Log::info($repose_arr);
        return [
            'code' => ErrorCode::THREE_FAIL,
            'errorMessage' => '微信下单失败:' . $repose_arr['return_msg'] ?? '',
        ];
    }

    /**
     * 退款申请
     * @param \Closure $call
     */
    public function refundApply($refund_order, $call)
    {
        $appid = $this->config['appId'];
        $mch_id = $this->config['mchId'];
        $data = [
            'appid' => $appid,
            'mch_id' => $mch_id,
            'nonce_str' => Str::random(32),
            'sign_type' => 'MD5',
            'transaction_id' => $refund_order->order->transaction_id,
            'out_refund_no' => $refund_order->out_refund_no,
            'total_fee' => $refund_order->order->total_fee,
            'refund_fee' => $refund_order->refund_fee,
            'refund_fee_type' => 'CNY',
            'refund_desc' => $refund_order->refund_reason,
            'notify_url' => route('wx-CallWxRefund'),
        ];
        $data['sign'] = $this->MakeSign($data);
        $xml = self::createXml($data);
        $repose_xml = $this->postXmlCurl($xml, self::getBaseUrl('/pay/unifiedorder'),true);
        $repose_arr = $this->fromXml($repose_xml);
        //通讯成功
        if (isset($repose_arr['return_code']) and $repose_arr['return_code'] == 'SUCCESS') {
            //业务成功
            if (isset($repose_arr['result_code']) and $repose_arr['result_code'] == 'SUCCESS') {
                //退款申请成功
               $flag=  $call([
                    'refund_no' =>$repose_arr['out_refund_no'],
                    'refund_id' =>$refund_order['refund_id'],
                    'cash_fee' => $repose_arr['settlement_refund_fee']/100,
                ]);
               if(!$flag){
                   Log::info('退款申请订单未找到！');
                   Log::info($repose_arr);
               }
               return $flag;
            }
        }
        return false;
    }

    /**
     * 退款结果通知
     * @param \Closure $call
     */
    public function refundNotice($call)
    {
        $xml = file_get_contents('php://input');
        if (!$xml) {
            return self::createXml([
                'return_code' => 'FAIL',
                'return_msg' => '验证失败'
            ]);
        }
        $http_params = $this->fromXml($xml);
        $appid = $this->config['appId'];
        $mch_id = $this->config['mchId'];
        //验证回调参数
        $tmp_sign = $this->MakeSign($http_params);
        if (isset($http_params['sign']) and $http_params['sign'] == $tmp_sign) {
            if (isset($http_params['return_code']) and $http_params['return_code'] == 'SUCCESS') {
                if (isset($http_params['result_code']) and $http_params['result_code'] == 'SUCCESS') {
                    if ($http_params['appid'] !== $appid) {
                        return self::createXml([
                            'return_code' => 'FAIL',
                            'return_msg' => 'appid不正确'
                        ]);
                    }
                    if ($http_params['mch_id'] !== $mch_id) {
                        return self::createXml([
                            'return_code' => 'FAIL',
                            'return_msg' => 'mch_id不正确'
                        ]);
                    }
                    $info = $this->decodeReqInfo($http_params['req_info']);
                    if (isset($info['refund_status']) and $info['refund_status'] == 'SUCCESS') {
                        //退款成功
                        $call([
                            'refund_no' => $info['out_refund_no'],
                            'refund_id' => $info['refund_id'],
                            'cash_fee' => $info['settlement_refund_fee'] / 100,
                            'refund_time' => strtotime($info['success_time']),
                        ]);
                        return self::createXml([
                            'return_code' => 'SUCCESS',
                            'return_msg' => '退款成功！'
                        ]);
                    }
                }
            }
        }
        Log::info('WX-refundNotice-FAIL');
        Log::info($xml);
        return self::createXml([
            'return_code' => 'FAIL',
            'return_msg' => '验证失败'
        ]);
    }

    /**
     * 解密退款的req_info字段
     * https://pay.weixin.qq.com/wiki/doc/api/wxa/wxa_api.php?chapter=9_16&index=10#menu1
     * @param $info
     * @return array
     */
    private function decodeReqInfo($info)
    {
        if (self::IS_SAND_BOX) {
            $key = $this->config['sandKey'];
        } else {
            $key = $this->config['appSecret'];
        }
        //2.对商户key做md5，得到32位小写key
        $md5Key = strtolower(md5($key));
        $xml=deCode($md5Key, $info,'aes-256-ecb');
        return  $this->fromXml($xml);
    }

    /**
     * 主动查询订单
     * @param $order
     * @param \Closure $closure
     * @return mixed|void
     */
    public function findOrder($order, \Closure $call)
    {

        $appid = $this->config['appId'];
        $mch_id = $this->config['mchId'];
        $url = self::getBaseUrl('/pay/orderquery');
        $post = [
            'appid' => $appid,
            'mch_id' => $mch_id,
            'out_trade_no' => $order->order_sn,
            'nonce_str' => Str::random(32),
            'sign_type' => 'MD5',
        ];
        $post['sign'] = $this->MakeSign($post);
        $xml = self::createXml($post);
        $repose_xml = $this->postXmlCurl($xml, $url);
        $repose_arr = $this->fromXml($repose_xml);
        if (self::DEBUG) {
            Log::info('searchOrderXML:');
            Log::info($repose_xml);
        }
        //通讯成功
        if (isset($repose_arr['return_code']) and $repose_arr['return_code'] == 'SUCCESS') {
            //业务成功
            if (isset($repose_arr['result_code']) and $repose_arr['result_code'] == 'SUCCESS') {
                if ($repose_arr['trade_state'] == 'SUCCESS') {
                    //有用代金券的情况下，应结订单金额作为回调金额
                    $calPrice = $repose_arr['settlement_total_fee'] ?? $repose_arr['total_fee'];
                    $call([
                        'prepay_id' => $repose_arr['transaction_id'],
                        'total_price' => $repose_arr['total_fee'] / 100,
                        'actual_payment' => $calPrice / 100,
                        'order_sn' => $repose_arr['out_trade_no'],
                        'pay_type' => 1,
                        'time_end' => $repose_arr['time_end'],
                    ]);
                    return true;
                }
            }
            return false;
        }
        return false;
    }

    /**
     * @return mixed|string
     * 获取沙箱key
     *
     */
    public function getkey()
    {
        //https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=23_1
        $url = self::getBaseUrl('/pay/getsignkey');
        $post = [
            'mch_id' => $this->config['mchId'],
            'nonce_str' => Str::random(32),
        ];
        $post['sign'] = $this->MakeSign($post);
        $xml = self::createXml($post);
        $repose_xml = $this->postXmlCurl($xml, $url);
        $repose_arr = $this->fromXml($repose_xml);
        //通讯成功
        if (isset($repose_arr['return_code']) and $repose_arr['return_code'] == 'SUCCESS') {
            //业务成功
            Log::info('sandbox_signkey:' . $repose_arr['sandbox_signkey']);
            return $repose_arr['sandbox_signkey'];
        }
        Log::info($repose_arr);
        return 'xx';
    }

    public static function createXml($data)
    {
        $xml = '<xml>';
        foreach ($data as $k => $val) {
            if (is_array($val)) {
                $st = json_encode($val);
                $xml .= '<' . $k . '><![CDATA[' . $st . ']]></' . $k . '>';
            } else {
                $xml .= '<' . $k . '>' . $val . '</' . $k . '>';
            }
        }
        return $xml . '</xml>';
    }

    /**
     * 将xml转为array
     * @param string $xml
     * @throws WxPayException
     */
    public static function fromXml($xml)
    {
        if (!$xml) {
            return array();
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }


    public function postXmlCurl($xml, $url, $useCert = false, $second = 30)
    {
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);

        curl_setopt($ch, CURLOPT_URL, $url);

        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);//严格校验
        }

        if ($useCert == true) {
            //设置证书
            //使用证书：cert 与 key 分别属于两个.pem文件
            //证书文件请放入服务器的非web目录下
            $sslCertPath = $this->config['sslCertPath'];
            $sslKeyPath = $this->config['sslKeyPath'];
            //  $config->GetSSLCertPath($sslCertPath, $sslKeyPath);
            curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLCERT, $sslCertPath);
            curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLKEY, $sslKeyPath);
        }

        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            throw new \Exception("curl出错，错误码:$error");
        }
    }

    /**
     * 格式化参数格式化成url参数
     */
    public function ToUrlParams($data)
    {
        $buff = "";
        foreach ($data as $k => $v) {
            if ($k != "sign" && $v) {
                if (is_array($v)) {
                    $buff .= $k . "=" . json_encode($v) . "&";
                } else {
                    $buff .= $k . "=" . $v . "&";
                }
            }
        }
        $buff = trim($buff, "&");
        return $buff;
    }

    /**
     * 生成签名
     * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
     */
    public function MakeSign($data)
    {
        //签名步骤一：按字典序排序参数
        ksort($data);
        $string = $this->ToUrlParams($data);
        //签名步骤二：在string后加入KEY
        if (self::IS_SAND_BOX) {
            $key = $this->config['sandKey'];
        } else {
            $key = $this->config['appSecret'];
        }
        $string = $string . "&key=" . $key;
        //签名步骤三：MD5加密
        $sig = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($sig);
        return $result;
    }

    public static function getBaseUrl($uri)
    {
        if (self::IS_SAND_BOX) {
            return 'https://api.mch.weixin.qq.com/sandboxnew' . $uri;
        }
        return 'https://api.mch.weixin.qq.com' . $uri;
    }

}
