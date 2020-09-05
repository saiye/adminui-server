<?php

namespace App\Http\Controllers\Wx;

use App\Models\PhysicsAddress;
use App\Models\QrCodePath;
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
        $channelId = $this->request->input('channelId');
        $width = $this->request->input('width');
        $hasDevice = PhysicsAddress::whereId($deviceShortId)->first();
        if (!$hasDevice) {
            return $this->json([
                'errorMessage' => '设备不存在!',
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
       $qrcodeModel=QrCodePath::whereDeviceId($deviceShortId)->whereChannelId($channelId)->whereWidth($width)->first();
        if($qrcodeModel){
            if($qrcodeModel->time>0 and $qrcodeModel->path){
                //小程序接口限制每分钟5000次，预防乱刷新，消耗二维码次数
                return $this->json([
                    'errorMessage' => '二维码未使用过,不刷新处理',
                    'code' => ErrorCode::SUCCESS,
                    'full_path' => $qrcodeModel->path,
                ]);
            }
        }
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
        return $this->json([
            'errorMessage' => 'success',
            'code' => ErrorCode::SUCCESS,
            'full_path' => $full_path,
        ]);
    }

}
