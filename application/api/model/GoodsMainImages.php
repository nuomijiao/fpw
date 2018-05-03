<?php
/**
 * Created by Sweet Jiao
 * User: Sweet Jiao
 * Date: 2018/4/28 0028
 * Time: 11:33
 */

namespace app\api\model;


class GoodsMainImages extends BaseModel
{
    protected $autoWriteTimestamp = true;

    protected $hidden = ['create_time', 'update_time', 'img_from', 'order'];

    public function getImgUrlAttr($value, $data)
    {
        return $this->prefixImgUrl($value, $data);
    }
}