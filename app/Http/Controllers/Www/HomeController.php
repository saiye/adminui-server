<?php
/**
 * Created by : PhpStorm
 * User: yuansai chen
 * Date: 2020/7/3
 * Time: 17:08
 */

namespace App\Http\Controllers\Www;


use App\Constants\ErrorCode;
use App\Constants\Logic;
use App\Constants\SmsAction;
use App\Models\User;
use App\Service\SmsApi\HandelSms;
use App\TraitInterface\ApiTrait;
use App\TraitInterface\BaseTrait;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class HomeController extends BaseController
{
    use ApiTrait, BaseTrait;

    public function home()
    {
        return 'hello boy';
    }

    /**
     * web端手机号码,进行注册
     */
    public function doPhoneReg(HandelSms $api)
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'area_code' => 'required',
            'phone' => 'required',
            'nickname' => 'required|max:20',
            'sex' => 'required|in:0,1',
            'phone_code' => 'required|numeric',
            'password' => ['required', 'min:6', 'max:18', 'regex:/^(?!^(\d+|[a-zA-Z]+|[~.!@#$%^&*?]+)$)^[\w~!@#$%\^&*.?]+$/'],
            'affirm_password' => 'required|min:6|max:18|same:password',//确认密码
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $area_code = $this->request->input('area_code');
        $phone = $this->request->input('phone');
        $phone_code = $this->request->input('phone_code');
        $nickname = $this->request->input('nickname');
        $sex = $this->request->input('sex');
        $password = $this->request->post('password');
        $res = $api->phoneCheck($area_code, $phone);
        if ($res['code'] !== 0) {
            return $this->json($res);
        }
        $user = User::whereAreaCode($area_code)->wherePhone($phone)->first();
        if ($user) {
            return $this->json([
                'errorMessage' =>trans('user.already_exist'),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        if ($api->checkCode('code',$area_code,$phone, $phone_code, SmsAction::USER_REG)) {
            //考虑到，用户变更手机的情况，账号随机串
            $account = time().mt_rand(1111,99999);
            $token = Str::random(32);
            $user = User::create([
                'phone' =>  $phone,
                'account' => $account,
                'sex' => $sex,
                'nickname' => $nickname,
                'area_code' =>$area_code,
                'password' => Hash::make($password),
                'type'=>Logic::USER_TYPE_PHONE,
                'token' => $token,
            ]);
            if ($user) {
                return $this->json([
                    'errorMessage' => trans('user.registered_successfully'),
                    'code' => ErrorCode::SUCCESS,
                    'token' => $token,
                ]);
            }
        }
        return $this->json([
            'errorMessage' =>trans('user.verification_code_invalid'),
            'code' => ErrorCode::VALID_FAILURE,
        ]);
    }
}
