<?php
/**
 * Created by Sweet Jiao
 * User: Sweet Jiao
 * Date: 2018/5/2 0002
 * Time: 15:12
 */

namespace app\lib\enum;


class GoodsCheckStatus
{
    //未审核
    const UNCHECK = 0;

    //审核不通过
    const CHECKUNPASS = 1;

    //审核通过
    const CHECKPASS = 2;
}