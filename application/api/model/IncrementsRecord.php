<?php
/**
 * Created by Sweet Jiao
 * User: Sweet Jiao
 * Date: 2018/5/7 0007
 * Time: 17:11
 */

namespace app\api\model;


class IncrementsRecord extends BaseModel
{
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;

    public static function checkBidValid($uid, $goodsID)
    {
        $bid = self::where([])
    }
}