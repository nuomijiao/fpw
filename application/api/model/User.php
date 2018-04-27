<?php
/**
 * Created by Sweet Jiao
 * User: Sweet Jiao
 * Date: 2018/4/20 0020
 * Time: 13:15
 */

namespace app\api\model;


class User extends BaseModel
{
    public static function checkMobile($mobile)
    {
        $user = self::where('mobile', '=', $mobile)->find();
        return $user;
    }

    public static function checkUser($ac, $se)
    {
        $user = self::where(['mobile'=>$ac, 'password'=>md5($se)])->find();
        return $user;
    }

}