<?php
/**
 * Created by Sweet Jiao
 * User: Sweet Jiao
 * Date: 2018/5/7 0007
 * Time: 16:27
 */

namespace app\api\model;


class AuctionEnroll extends BaseModel
{
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;

    public static function isEnroll($uid, $goodsID)
    {
        $isEnroll = self::where(['user_id' => $uid, 'goods_id' => $goodsID])->find();
        if ($isEnroll) {
            return 1;
        } else {
            return 0;
        }
    }

}