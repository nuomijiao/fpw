<?php
/**
 * Created by Sweet Jiao
 * User: Sweet Jiao
 * Date: 2018/4/28 0028
 * Time: 11:02
 */

namespace app\api\model;

use app\api\model\GoodsHits as GoodsHitsModel;
use app\api\model\AuctionEnroll as AuctionEnrollModel;

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

    public function goodsHits()
    {
        return $this->hasMany('GoodsHits', 'goods_id', 'id');
    }

    public function auctionEnroll()
    {
        return $this->hasMany('AuctionEnroll', 'goods_id', 'id');
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

    public static function getGoodsDetail($goodsID, $uid = '')
    {
        $goods = self::with([
            'mainImg' => function($query){
                $query->field(['img_url'=>'image', 'goods_id', 'id', 'img_from', 'order', 'create_time', 'update_time']);
            }
        ])->with(['detailImg'])->find($goodsID);
        $goodsDetail = $goods;
        $goodsCount = GoodsHitsModel::getClickCount($goodsID);
        $goodsDetail['click_count'] = $goodsCount;
        if (!empty(trim($uid))) {
            $isEnroll = AuctionEnrollModel::isEnroll($uid, $goodsID);
            $goodsDetail['is_enroll'] = $isEnroll;
        } else {
            $goodsDetail['is_enroll'] = 0;
        }
        return $goodsDetail;
    }

    public static function getGoodsInfo($id, $array = [])
    {
        $goods = self::field($array)->find($id);
        return $goods;
    }

}