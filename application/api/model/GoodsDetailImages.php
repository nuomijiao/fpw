<?php
/**
 * Created by Sweet Jiao
 * User: Sweet Jiao
 * Date: 2018/4/28 0028
 * Time: 11:34
 */

namespace app\api\model;


class GoodsDetailImages extends BaseModel
{
    protected $autoWriteTimestamp = true;

    protected $hidden = ['create_time', 'update_time', 'img_from', 'order', 'goods_id'];

    public function getImgUrlAttr($value, $data)
    {
        return $this->prefixImgUrl($value, $data);
    }
}