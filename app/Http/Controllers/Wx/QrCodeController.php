<?php

namespace App\Http\Controllers\Wx;


use App\Models\Device;
use App\Models\PhysicsAddress;
use App\Service\LoginApi\WeiXinLoginLoginApi;
use GuzzleHttp\Client;
use App\Constants\ErrorCode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class QrCodeController extends Base
{

    /**
     * 生成设备小程序的二维码
     */
    public function image(WeiXinLoginLoginApi $api)
    {

        $validator = $this->validationFactory->make($this->request->all(), [
            'scene' => 'required|max:32',
            'width' => 'required|numeric|max:1280|min:280',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $data = $this->request->input();

        $sceneData = scene_decode($data['scene']);

        if (!array_key_exists('deviceShortId', $sceneData)) {
            return $this->json([
                'errorMessage' => 'scene 参数有误！',
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $deviceShortId = $sceneData['deviceShortId'];
        if (!is_numeric($deviceShortId)) {
            return $this->json([
                'errorMessage' => 'deviceShortId必须是一个数字！',
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $hasDevice = PhysicsAddress::whereId($deviceShortId)->first();
        if (!$hasDevice) {
            return $this->json([
                'errorMessage' => 'device cant find!',
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        if ($hasDevice->qrCodePath) {
            $full_path = Storage::disk('public')->url($hasDevice->qrCodePath);
        } else {
            $imageRes = $api->getUnlimited($data);
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
            'scene' => scene_encode([
                'deviceShortId' => 1024,
            ]),
            'width' => 300,
        ];

        $client = new Client([
            // 'handler' => HandlerStack::create(new CoroutineHandler()),
            'timeout' => 5,
            'swoole' => [
                'timeout' => 10,
                'socket_buffer_size' => 1024 * 1024 * 2,
            ],
        ]);
        $response = $client->post($url, [
            'form_params' =>$data,
        ]);
        if ($response->getStatusCode() == 200) {
            $res= json_decode($response->getBody()->getContents(),true);
            if(isset($res['code']) and $res['code']==0){
                return $res['full_path'];
            }
        } else {
            return 'cant create erCode';
        }
    }


}
