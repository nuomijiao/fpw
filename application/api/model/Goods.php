<?php
/**
 * Created by Sweet Jiao
 * User: Sweet Jiao
 * Date: 2018/4/28 0028
 * Time: 11:02
 */

namespace app\api\model;


class Goods extends BaseModel
{

    protected $autoWriteTimestamp = true;

    protected $hidden = ['update_time', 'create_time', 'recycle'];

    public function getCheckStatusAttr($value)
    {
        $status = [0 => '未审核', 1=> '审核不通过', 2=> '审核通过'];
        return $status[$value];
    }

    public function mainImg()
    {
        return $this->hasMany('GoodsMainImages', 'goods_id', 'id');
    }

    public function detailImg()
    {
        return $this->hasMany('GoodsDetailImages', 'goods_id', 'id');
    }

    public static function getAllByUser($uid, $page, $size)
    {
        $pagingData = self::with(['mainImg', 'detailImg'])->where('user_id', '=', $uid)->paginate($size, true, ['page' => $page]);
        return $pagingData;
    }

    public static function getAll($page, $size)
    {
        $pagingData = self::with(['mainImg', 'detailImg'])->paginate($size, true, ['page' => $page]);
        return $pagingData;
    }

    public static function getGoodsDetail($id)
    {
        $goods = self::with([
            'mainImg' => function($query){
                $query->field(['img_url'=>'image', 'goods_id', 'id', 'img_from', 'order', 'create_time', 'update_time']);
            }
        ])->with(['detailImg'])->find($id);
        return $goods;
    }
}