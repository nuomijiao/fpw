<?php
/**
 * Created by Sweet Jiao
 * User: Sweet Jiao
 * Date: 2018/5/7 0007
 * Time: 18:42
 */

namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\service\Enroll as EnrollService;
use app\api\service\Token as TokenService;
use app\api\validate\EnrollNew;


class AuctionEnroll extends BaseController
{
    public function enroll($goods_id = '', $address_id)
    {
        (new EnrollNew())->goCheck();
        $uid = TokenService::getCurrentUid();
        $enrollOrder = new EnrollService();
        $enroll = $enrollOrder->placeEnroll($uid, $goods_id, $address_id);
        return json([
            'error_code' => 'ok',
            'enroll' => $enroll,
        ]);
    }
}