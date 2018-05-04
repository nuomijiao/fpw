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
use app\api\validate\WeiXinTokenGet;

class Token extends BaseController
{
    public function getLoginToken($mobile = '', $pwd = '')
    {
        (new LoginTokenGet())->goCheck();
        $log_reg = new LoginToken();
        $token = $log_reg->get($mobile, $pwd);
        return json([
            'error_code' => 'ok',
            'token'=> $token,
        ]);
    }


    public function getWeiXinToken($code = '', $state)
    {
        $request = (new WeiXinTokenGet())->goCheck();
        $param = $request->param();
        return json([
            'code' => $param['code'],
            'state' => $param['state'],
        ]);
    }

    public function verifyToken()
    {

    }
}