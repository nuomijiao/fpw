<?php
/**
 * Created by Sweet Jiao
 * User: Sweet Jiao
 * Date: 2018/4/26 0026
 * Time: 10:18
 */

namespace app\api\controller\v1;

use app\api\controller\BaseController;
use app\api\model\Goods as GoodsModel;
use app\api\model\TmpPic as TmpPicModel;
use app\api\service\Token as TokenService;
use app\api\validate\GoodsNew;
use app\api\validate\PictureNew;
use app\lib\exception\GoodsException;
use app\lib\exception\ParameterException;
use app\lib\exception\SuccessMessage;
use think\Exception;

class AddGoods extends BaseController
{
    public function uploadImg()
    {
        $request = (new PictureNew())->goCheck('upload');
        //根据Token来获取uid
        $uid = TokenService::getCurrentUid();
        $pic = $request->file('goods_pic');
        $pic_type = $request->param('pic_type');
        //图片原始名称
        $origin_info = $pic->getInfo();
        $info = $pic->rule('uniqid')->move(ROOT_PATH.'public_html'.DS.'tmp_pic');
        if ($info) {
            $dataArray = [
                'user_id' => $uid,
                'img_url' => $info->getSaveName(),
                'pic_type' => $pic_type,
                'origin_name' => $origin_info['name'],
            ];
            TmpPicModel::create($dataArray);
            return json([$info->getSaveName(), $pic_type]);
        } else {
            throw new Exception($info->getError());
        }
    }

    public function deleteTmpPic($name)
    {
        if (empty(trim($name))) {
            throw new ParameterException();
        }
        $uid = TokenService::getCurrentUid();
        $picInfo = TmpPicModel::getInfoByName($uid, $name);
        if ($picInfo->isEmpty()) {
            throw new GoodsException();
        } else {
            $picArray = $picInfo->toArray();
            $picName = [];
            foreach ($picArray as $item) {
                array_push($picName, $item['']);
            }
        }

    }

    public function addGoods()
    {
        $validate = new GoodsNew();
        $request = $validate->goCheck();

        $uid = TokenService::getCurrentUid();

        $dataArray = $validate->getDataByRule($request->post());
        $goodsArray = array_merge($dataArray, ['user_id' => $uid]);
        unset($goodsArray['main_img_url'], $goodsArray['detail_img_url']);

        $goods = GoodsModel::create($goodsArray);

        //移动临时文件夹的图片，并返回移动后的路径
        $lastDataArray = $this->moveTmpPic($dataArray);

        $goods->mainImg()->saveAll($lastDataArray['main_img_url']);
        $goods->detailImg()->saveAll($lastDataArray['detail_img_url']);

        throw new SuccessMessage();
    }

    private function moveTmpPic($dataArray)
    {
        $main_img_url = [];
        $detail_img_url = [];
        foreach ($dataArray['main_img_url'] as $item) {
            rename(ROOT_PATH.'public_html'.DS.'tmp_pic'.DS.$item['img_url'], ROOT_PATH.'public_html'.DS.'goods_pic'.DS.$item['img_url']);
            array_push($main_img_url, ['img_url'=> "/goods_pic/".$item['img_url']]);
        }
        foreach ($dataArray['detail_img_url'] as $item) {
            rename(ROOT_PATH.'public_html'.DS.'tmp_pic'.DS.$item['img_url'], ROOT_PATH.'public_html'.DS.'goods_pic'.DS.$item['img_url']);
            array_push($detail_img_url, ['img_url' => "/goods_pic/".$item['img_url']]);
        }

        $lastDataArray = ['main_img_url'=>$main_img_url, 'detail_img_url'=>$detail_img_url];
        return $lastDataArray;
    }
}