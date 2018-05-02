<?php
/**
 * Created by Sweet Jiao
 * User: Sweet Jiao
 * Date: 2018/4/28 0028
 * Time: 21:18
 */

namespace app\api\service;

use app\api\model\Goods as GoodsModel;
use app\api\model\TmpPic as TmpPicModel;
use app\lib\exception\GoodsException;
use think\Exception;

class Goods
{
    private $uid;

    function __construct($uid = '')
    {
        if (!$uid) {
            throw new Exception('用户id不能为空');
        }
        $this->uid = $uid;
    }

    public function add($dataArray)
    {
        $goodsArray = array_merge($dataArray, ['user_id' => $this->uid]);
        unset($goodsArray['main_img_url'], $goodsArray['detail_img_url']);

        $goods = GoodsModel::create($goodsArray);

        //移动临时文件夹的图片，并返回移动后的路径
        $lastDataArray = $this->moveTmpPic($dataArray);

        $goods->mainImg()->saveAll($lastDataArray['main_img_url']);
        $goods->detailImg()->saveAll($lastDataArray['detail_img_url']);
    }

    private function moveTmpPic($dataArray)
    {
        $main_img_url = [];
        $detail_img_url = [];
        $preDelImg = [];
        foreach ($dataArray['main_img_url'] as $item) {
            rename(ROOT_PATH.'public_html'.DS.'tmp_pic'.DS.$item['img_url'], ROOT_PATH.'public_html'.DS.'goods_pic'.DS.$item['img_url']);
            array_push($main_img_url, ['img_url'=> "/goods_pic/".$item['img_url'], 'img_from'=>1]);
            array_push($preDelImg, $item['img_url']);
        }
        foreach ($dataArray['detail_img_url'] as $item) {
            rename(ROOT_PATH.'public_html'.DS.'tmp_pic'.DS.$item['img_url'], ROOT_PATH.'public_html'.DS.'goods_pic'.DS.$item['img_url']);
            array_push($detail_img_url, ['img_url' => "/goods_pic/".$item['img_url'], 'img_from'=>1]);
            array_push($preDelImg, $item['img_url']);
        }

        //删除tmp_pic表临时图 数据
        TmpPicModel::delTmpPicByImgUrl($preDelImg);

        $lastDataArray = ['main_img_url'=>$main_img_url, 'detail_img_url'=>$detail_img_url];
        return $lastDataArray;
    }

    public function delTmpPic($name)
    {
        $picInfo = TmpPicModel::getInfoByName($this->uid, $name);
        if ($picInfo->isEmpty()) {
            throw new GoodsException();
        } else {
            $picArray = $picInfo->toArray();
            $picImgUrl = [];
            foreach ($picArray as $item) {
                unlink(ROOT_PATH.'public_html'.DS.'tmp_pic'.DS.$item['img_url']);
                array_push($picImgUrl, $item['img_url']);
            }
            TmpPicModel::DelTmpPicByImgUrl($picImgUrl);
        }
    }

    public function checkIsImg($fileInfo)
    {
        $ext = pathinfo($fileInfo['name'],PATHINFO_EXTENSION);
        if (in_array($ext, ['jpg', 'png', 'gif', 'JPG', 'PNG', 'GIF']) && in_array($fileInfo['type'], ['image/jpeg', 'image/png', 'image/gif'])) {
            return true;
        } else {
            return false;
        }
    }

}