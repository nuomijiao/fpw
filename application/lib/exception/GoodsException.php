<?php
/**
 * Created by Sweet Jiao
 * User: Sweet Jiao
 * Date: 2018/4/26 0026
 * Time: 13:56
 */

namespace app\lib\exception;


class GoodsException extends BaseException
{
    public $msg = '图片已被删除或不存在';
    public $errorCode = 30001;
}