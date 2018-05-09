<?php
/**
 * Created by Sweet Jiao
 * User: Sweet Jiao
 * Date: 2018/5/9 0009
 * Time: 14:51
 */

namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\service\WxPay as WxPayService;
use app\api\validate\IDMustBePostiveInt;

class WxPay extends BaseController
{
    public function getEnrollPreOrder($id = '')
    {
        (new IDMustBePostiveInt())->goCheck();
        $pay = new WxPayService($id, 'enroll');
        return $pay->payEnroll();
    }
}