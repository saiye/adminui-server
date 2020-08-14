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

    const postOrderUrl = 'https://api.mch.weixin.qq.com';

    // const postOrderUrl = 'https://api.mch.weixin.qq.com/sandboxnew';
    public function init()
    {
        $this->config = Config::get('pay.key.wx');
        // $this->config['appSecret']=$this->getkey();
    }

    /*
     * 微信支付回调
     */
    function callBack($call)
    {
        $xml = file_get_contents('php://input');
        $http_params = $this->fromXml($xml);
        if ($xml) {
            Log::info('wxPayCallL:');
            Log::info($xml);
        }
        //验证回调参数
        $tmp_sign = $this->MakeSign($http_params);
        if (isset($http_params['sign']) and $http_params['sign'] == $tmp_sign) {
            //是否支付ok?
            if (isset($http_params['return_code']) and $http_params['return_code'] == 'SUCCESS') {
                if (isset($http_params['result_code']) and $http_params['result_code'] == 'SUCCESS') {
                    $call([
                        'prepay_id' => $http_params['transaction_id'],
                        'callPrice' => $http_params['total_fee'] / 100,
                        'order_sn' => $http_params['out_trade_no'],
                        'pay_type' => 1,
                    ]);
                    //验证ok
                    return self::createXml([
                        'return_code' => 'SUCCESS',
                        'return_msg' => 'OK'
                    ]);
                }
            }
        }
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
        $price = $order['actual_payment'] * 100;
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
        $repose_xml = self::postXmlCurl($xml, self::postOrderUrl . '/pay/unifiedorder');
        $repose_arr = $this->fromXml($repose_xml);
        //通讯成功
        if (isset($repose_arr['return_code']) and $repose_arr['return_code'] == 'SUCCESS') {
            //业务成功
            if (isset($repose_arr['result_code']) and $repose_arr['result_code'] == 'SUCCESS') {
                //预充值订单
                $sendData=[
                        'appId'=>$appid,
                        'timeStamp'=>time(),
                        'nonceStr'=>Str::random(32),
                        'package'=>'prepay_id='.$repose_arr['prepay_id'],
                        'signType'=>'MD5',
                ];
                $sendData['paySign']=$this->MakeSign($sendData);
                return [
                    'code' => ErrorCode::SUCCESS,
                    'errorMessage' => '微信下单成功',
                    'data'=>$sendData,

                ];
            }
            Log::info('原生微信下单失败-业务失败' . $order->order_sn);
            Log::info($repose_arr);
            return [
                'code' => ErrorCode::THREE_FAIL,
                'errorMessage' => '微信下单失败-业务失败',
            ];
        }
        Log::info('原生微信下单失败' . $order->order_sn);
        Log::info($repose_arr);
        return [
            'code' => ErrorCode::THREE_FAIL,
            'errorMessage' => '微信下单失败',
        ];
    }

    /**
     * 退款
     * @param \Closure $call
     */
    public function  refundApply($refund_order,$call){
        $data=[
            'appid'=>'',
            'mch_id'=>'',
            'nonce_str'=>'',
            'sign_type'=>'MD5',
            'transaction_id'=>'',
            'out_refund_no'=>'',
            'total_fee'=>'',
            'refund_fee'=>'',
            'refund_fee_type'=>'CNY',
            'refund_desc'=>'',
            'notify_url'=>'',
        ];
        $data['sign']=$this->MakeSign($data);
        $xml = self::createXml($data);
        $repose_xml = self::postXmlCurl($xml, self::postOrderUrl . '/pay/unifiedorder');
        $repose_arr = $this->fromXml($repose_xml);
        //通讯成功
        if (isset($repose_arr['return_code']) and $repose_arr['return_code'] == 'SUCCESS') {
            //业务成功
            if (isset($repose_arr['result_code']) and $repose_arr['result_code'] == 'SUCCESS') {
                //退款申请成功
                $call([
                    'out_refund_no'=>'',
                    'refund_id'=>'',
                    'refund_fee'=>'',
                ]);
            }
        }
    }

    /**
     * 退款结果通知
     * @param \Closure $call
     */
    public function refundNotice($call){

    }

    /**
     * @return mixed|string
     * 获取沙箱key
     *
     */
    public function getkey()
    {
        //https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=23_1
        $url = self::postOrderUrl . '/pay/getsignkey';
        $post = [
            'mch_id' => $this->config['mchId'],
            'nonce_str' => Str::random(32),
        ];
        $post['sign'] = $this->MakeSign($post);
        $xml = self::createXml($post);
        $repose_xml = self::postXmlCurl($xml, $url);
        $repose_arr = $this->fromXml($repose_xml);
        //通讯成功
        if (isset($repose_arr['return_code']) and $repose_arr['return_code'] == 'SUCCESS') {
            //业务成功
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


    public static function postXmlCurl($xml, $url, $useCert = false, $second = 30)
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
        $string = $string . "&key=" . $this->config['appSecret'];
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

}
