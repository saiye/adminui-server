<?php

namespace App\Http\Controllers\Wx;

use App\Models\PhysicsAddress;
use App\Service\LoginApi\LoginApi;
use GuzzleHttp\Client;
use App\Constants\ErrorCode;
use Illuminate\Support\Facades\Storage;


class QrCodeController extends Base
{

    /**
     * 生成设备小程序的二维码
     */
    public function image(LoginApi  $api)
    {

        $validator = $this->validationFactory->make($this->request->all(), [
            'deviceShortId' => 'required|numeric|min:1',
            'channelId' => 'required|numeric|min:1',
            'width' => 'required|numeric|max:1280|min:280',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $data = $this->request->input();
        $deviceShortId = $this->request->input('deviceShortId');
        $hasDevice = PhysicsAddress::whereId($deviceShortId)->first();
        if (!$hasDevice) {
            return $this->json([
                'errorMessage' => '设置不存在!',
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $full_path='';
        if ($hasDevice->qrCodePath) {
            if(Storage::exists($hasDevice->qrCodePath)){
                $full_path = Storage::url($hasDevice->qrCodePath);
            }
        }
        if(!$full_path) {
            $data['type']=1;//表示登录游戏
            $imageRes = $api->getQrCode($data);
            if ($imageRes) {
                $full_path = $imageRes['full_path'];
            } else {
                return $this->json([
                    'errorMessage' => '二维码生成失败',
                    'code' => ErrorCode::CREATE_ERCODE_ERROR,
                ]);
            }
        }
        return $this->json([
            'errorMessage' => 'success',
            'code' => ErrorCode::SUCCESS,
            'full_path' => $full_path,
        ]);
    }


    public function testQrCode()
    {

        $url = route('wx-QrCodeImage');
        $data = [
            'deviceShortId' => 1113,
            'channelId' => 1,
            'width' => 300,
        ];
        $client = new Client([
            // 'handler' => HandlerStack::create(new CoroutineHandler()),
            'timeout' => 5,
            'verify' => false,
            'swoole' => [
                'timeout' => 10,
                'socket_buffer_size' => 1024 * 1024 * 2,
            ],
        ]);
        $response = $client->post($url, [
            'form_params' => $data,
        ]);
        if ($response->getStatusCode() == 200) {
            $res = json_decode($response->getBody()->getContents(), true);
            if (isset($res['code']) and $res['code'] == 0) {
                return $res['full_path'];
            }
            return $res['errorMessage'];
        } else {
            return 'cant create erCode';
        }
    }


}
