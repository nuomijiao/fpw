<?php
/**
 * Created by Sweet Jiao
 * User: Sweet Jiao
 * Date: 2018/4/20 0020
 * Time: 13:15
 */

namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\validate\RegisterNew;
use app\api\model\User as UserModel;
use app\lib\enum\SmsCodeTypeEnum;
use app\lib\exception\LogAndRegException;
use app\api\model\SmsCode as SmsCodeModel;
use app\api\service\LoginToken;

class Register extends BaseController
{
    public function register($mobile = '', $pwd = '', $code = '')
    {
        //注册流程
        //1. 判断手机号码是否已被注册
        //2.判断验证码是否正确
        //3.账号密码加密新增数据库
        //4.注册成功，即生成token返回给客户端，保存登陆状态
        (new RegisterNew())->goCheck();
        //检查手机号码是否被注册
        $user = UserModel::checkMobile($mobile);
        if ($user) {
            throw new LogAndRegException();
        }
        //检查验证码是否正确
        $codeInfo = SmsCodeModel::checkCode($mobile, $code, SmsCodeTypeEnum::ToRegister);
        if (!$codeInfo || $codeInfo['code'] != $code || $codeInfo['expire_time'] < time() || 1 == $codeInfo['is_use']) {
            throw new LogAndRegException([
                'msg' => '验证码不匹配或已过期',
                'errorCode' => 20002,
            ]);
        } else {
            $timenow = time();
            //修改验证码使用状态
            SmsCodeModel::changeStatus($mobile, $code, SmsCodeTypeEnum::ToRegister, $timenow);
            //新增用户数据库
            $dataArray = [
                'mobile' => $mobile, 'password' => md5($pwd), 'last_login' => $timenow,
            ];
            $user = UserModel::create($dataArray);
            if ($user->id) {
                $reg = new LoginToken();
                $token = $reg->get($mobile, $pwd, 'register', $user->id);
                return json([
                    'errorCode' => 'ok',
                    'token' => $token,
                ]);
            }
        }
    }
}