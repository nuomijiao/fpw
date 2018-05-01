<?php
/**
 * Created by Sweet Jiao
 * User: Sweet Jiao
 * Date: 2018/5/1 0001
 * Time: 8:43
 */

namespace app\api\model;


class Address extends BaseModel
{
    public static function selectDefaultById($uid, $id)
    {
        self::where('user_id', '=', $uid)->update(['is_moren' => 0]);
        self::update(['id' => $id, 'is_moren' => 1]);
    }
}