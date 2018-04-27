<?php
/**
 * Created by Sweet Jiao
 * User: Sweet Jiao
 * Date: 2018/4/26 0026
 * Time: 13:25
 */

namespace app\api\model;


use app\lib\enum\UploadImgTypeEnum;
use think\Model;

class TmpPic extends Model
{
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;

    public function setPicTypeAttr($value)
    {
        if ($value == 'MainImg') {
            return UploadImgTypeEnum::MainImg;
        } else if ($value == 'DetailImg') {
            return UploadImgTypeEnum::DetailImg;
        }
    }
}