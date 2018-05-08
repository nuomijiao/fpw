<?php
/**
 * Created by Sweet Jiao
 * User: Sweet Jiao
 * Date: 2018/5/7 0007
 * Time: 20:41
 */

namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\validate\IDMustBePostiveInt;
use app\api\service\Token as TokenService;
use app\api\model\IncrementsRecord as IncrementsRecordModel;
use app\api\model\Goods as GoodsModel;

class Bid extends BaseController
{
    public function bid($id = '')
    {
        (new IDMustBePostiveInt())->goCheck();
        $uid = TokenService::getCurrentUid();
        //获取商品的信息
        $goods = GoodsModel::getGoodsInfo($id, ['increments_price', 'end_time']);

        //检查是否可以竞价
        IncrementsRecordModel::checkBidValid($uid, $id);

    }
}