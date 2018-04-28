<?php
/**
 * Created by Sweet Jiao
 * User: Sweet Jiao
 * Date: 2018/4/24 0024
 * Time: 16:18
 */

namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\service\LoginToken;
use app\api\validate\LoginTokenGet;

class Token extends BaseController
{
    public function getLoginToken($mobile = '', $pwd = '')
    {
        (new LoginTokenGet())->goCheck();
        $log_reg = new LoginToken();
        $token = $log_reg->get($mobile, $pwd);
        return json([
            'errorCode' => 'ok',
            'token'=> $token,
        ]);
    }

    public function verifyToken()
    {

    }
}