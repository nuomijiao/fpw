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

    protected $hidden = ['update_time', 'create_time'];

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
        $pagingData = self::with([
            'mainImg' => function($query){
                $query->limit(1);
            }
        ])->with('detailImg')->where('user_id', '=', $uid)->paginate($size, true, ['page' => $page]);
        return $pagingData;
    }
}