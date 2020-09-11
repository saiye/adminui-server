<?php
/**
 * Created by : PhpStorm
 * User: yuansai chen
 * Date: 2020/8/5
 * Time: 17:20
 */

namespace App\Service\SceneAction;

use App\Constants\ErrorCode;
use App\Models\Channel;
use App\Models\Device;
use App\Models\QrCodePath;
use App\Models\User;
use App\Service\GameApi\LrsApi;
use Illuminate\Support\Facades\Auth;

/**
 *
 * 扫描登录游戏
 *
 * Class Action1
 * @package App\Service\SceneAction
 */
class Action1 extends SceneBase
{
    public function run()
    {
        $validator2 = $this->validationFactory->make($this->data, [
            'id' => 'required|numeric',
            'time' => 'required|numeric|min:1',
        ]);
        if ($validator2->fails()) {
            return $this->json([
                'errorMessage' => '二维码有问题:' . $validator2->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $user =Auth::guard('wx')->user();
        if(!$user){
            return $this->json([
                'errorMessage' => '你未登录!',
                'code' => ErrorCode::ACCOUNT_NOT_LOGIN,
            ]);
        }
        $id = $this->data['id'];
        $hasQrCodeModel=QrCodePath::whereId($id)->first();
        if(!$hasQrCodeModel){
            return $this->json([
                'errorMessage' => '找不到二维码!',
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $deviceShortId =  $hasQrCodeModel->device_id;
        $channelId = $hasQrCodeModel->channel_id;
        $time = $this->data['time'];
        $device = Device::whereDeviceId($deviceShortId)->first();
        if (!$device) {
            return $this->json([
                'errorMessage' => '设备未绑定房间',
                'code' => ErrorCode::DEVICE_NOT_BINDING,
            ]);
        }
        if ($device->seat_num == 0 and $user->judge !== 1) {
            return $this->json([
                'errorMessage' => '普通账号,无法登陆法官设备',
                'code' => ErrorCode::ACCOUNT_NO_PREVILEGE,
            ]);
        }
        $channel = Channel::whereChannelId($channelId)->first();
        if ($channel) {
           if( $hasQrCodeModel->time!==$time){
                return $this->json([
                    'errorMessage' => '二维码已过期!',
                    'code' => ErrorCode::REPETITION_CODE,
                ]);
            }
            $hasQrCodeModel->time=0;
            $hasQrCodeModel->save();
            //更新最后登录的渠道
            User::whereId($user->id)->update([
                'channel_id' => $channelId,
            ]);
            return (new LrsApi($channel))->loginCallBack([
                "deviceShortId" => $device->device_id,
                "account" => $user->account,
                "userId" => $user->id,
                "name" => $user->nickname,
                "sex" => $user->sex,
                "icon" => $user->icon ?? '',
                "bigIcon" => $user->big_icon ?? '',
                "roomId" => $device->room_id, // [可选] 房间唯一id
                "dupId" => $device->room->dup_id, // [可选] 房间对于dupId
                "judge" => $device->seat_num == 0 ? 1 : 0, // [可选] 是否是法官，0否 1是
                "seatIdx" => $device->seat_num, // [可选] 座位号，法官为0，其他从1开始
                "deviceMqttTopic" => $device->room->deviceMqttTopic ?? '', // [可选]房间设备mqtt主题
            ]);
        }
        return $this->json([
            'errorMessage' => '渠道' . $channelId . '不存在！',
            'code' => ErrorCode::CHANNEL_NONENTITY,
        ]);
    }
}
