<?php
/**
 * Created by Sweet Jiao
 * User: Sweet Jiao
 * Date: 2018/4/24 0024
 * Time: 16:20
 */

namespace app\api\validate;


class LoginTokenGet extends BaseValidate
{
    protected $rule = [
        'ac' => 'require|isMobile',
        'se' => 'require|isNotEmpty'
    ];

    protected $message = [
        'ac' => '请填写正确的手机号',
        'se' => '密码不能为空',
    ];
}