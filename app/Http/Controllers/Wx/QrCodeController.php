<?php

namespace App\Http\Controllers\Wx;


use App\Models\Device;
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

       /* $validator = $this->validationFactory->make($this->request->all(), [
            'scene' => 'required|max:32',
            'width' => 'required|numeric|max:1280|min:280',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }*/

      //  $data=$this->request->input();


        $data = [
            'scene' => scene_encode([
                'deviceShortId' => 1025,
            ]),
            'width' => 300,
        ];
        $sceneData = scene_decode($data['scene']);

        if (!array_key_exists('deviceShortId', $sceneData)) {
            return $this->json([
                'errorMessage' => 'scene 参数有误！',
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $url = $api->getUnlimited($data);
        if($url){
            $fullUrl = Storage::url($url);
            return $this->json([
                'errorMessage' => 'success',
                'code' => ErrorCode::SUCCESS,
                'url' => $fullUrl,
            ]);
        }
        return $this->json([
            'errorMessage' => '二维码生成失败',
            'code' => ErrorCode::CREATE_ERCODE_ERROR,
        ]);

    }


    public function testQrCode()
    {

        $url = route('wx-QrCodeImage');
        $data = [
            'scene' => scene_encode([
                'deviceShortId' => 1025,
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
            'json' => $data,
        ]);
        if ($response->getStatusCode() == 200) {
            return $this->json([
                'errorMessage' => 'success',
                'code' => 0,
                'body' => $response->getBody()->getContents(),
            ]);
        } else {
            return $this->json([
                'errorMessage' => 'error',
                'code' => -1,
                'body' => $response->getBody()->getContents(),
            ]);
        }
    }


}
