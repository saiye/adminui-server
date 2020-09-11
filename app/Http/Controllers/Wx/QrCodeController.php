<?php

namespace App\Http\Controllers\Wx;

use App\Models\PhysicsAddress;
use App\Models\QrCodePath;
use App\Service\LoginApi\LoginApi;
use GuzzleHttp\Client;
use App\Constants\ErrorCode;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\LabelAlignment;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Response\QrCodeResponse;

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
       $qrcodeModel=QrCodePath::whereDeviceId($deviceShortId)->whereChannelId($channelId)->whereClient(0)->whereWidth($width)->first();
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

    public function  appQrCode(){
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
        $time=time();
        $qrcodeModel=QrCodePath::whereDeviceId($deviceShortId)->whereChannelId($channelId)->whereClient(1)->whereWidth($width)->first();
        if($qrcodeModel){
            if($qrcodeModel->time>0 and $qrcodeModel->path){
                //小程序接口限制每分钟5000次，预防乱刷新，消耗二维码次数
                return $this->json([
                    'errorMessage' => '二维码未使用过,不刷新处理',
                    'code' => ErrorCode::SUCCESS,
                    'full_path' => $qrcodeModel->path,
                ]);
            }
        }else{
            $qrcodeModel=QrCodePath::create([
                'device_id'=>$deviceShortId,
                'channel_id'=>$channelId,
                'width'=>$width,
                'client'=>1,
                'type'=>1,
                'time'=>$time,
            ]);
        }
        $env=Config::get('app.env');
        //进游戏的二维码
        $type=1;
        $sceneData = scene_encode([
            'id' => $qrcodeModel->id,
            't' => $type,
            'env'=>$env,
            'time' =>$time,
        ]);
        $url='https://www.baidu.com/?'.$sceneData;
        $qrCode = new QrCode($url);
        $qrCode->setSize(280);
        $qrCode->setMargin(10);
        $qrCode->setWriterByName('png');
        $qrCode->setEncoding('UTF-8');
        $qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH());
        $qrCode->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0]);
        $qrCode->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0]);
       // $qrCode->setLabel('Scan the code', 16, __DIR__.'/../assets/fonts/noto_sans.otf', LabelAlignment::CENTER());
        $qrCode->setLogoPath(public_path('logo.png'));
        $qrCode->setLogoSize(80, 80);
        $qrCode->setValidateResult(false);
        $qrCode->setRoundBlockSize(true, QrCode::ROUND_BLOCK_SIZE_MODE_MARGIN);
        $qrCode->setRoundBlockSize(true, QrCode::ROUND_BLOCK_SIZE_MODE_ENLARGE);
        $qrCode->setRoundBlockSize(true, QrCode::ROUND_BLOCK_SIZE_MODE_SHRINK);
        $qrCode->setWriterOptions(['exclude_xml_declaration' => true]);

        $image_path = 'app/'.$env.'/qrCode/'.date('YmdHis').mt_rand(1,999).'.png';
        Storage::put($image_path, $qrCode->writeString());
        $full_path = Storage::url($image_path);
        //二维码入库
        if($qrcodeModel->path){
            Storage::delete($qrcodeModel->path);
        }
        $qrcodeModel->path=$image_path;
        $qrcodeModel->time=$time;
        $qrcodeModel->type=$type;
        $qrcodeModel->save();

       return $this->json([
            'errorMessage' => 'success',
            'code' => ErrorCode::SUCCESS,
            'full_path' => $full_path,
        ]);
    }

}
