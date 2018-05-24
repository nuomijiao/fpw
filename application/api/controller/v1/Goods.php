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
use app\api\model\GoodsHits as GoodsHitsModel;
use app\api\model\TmpPic as TmpPicModel;
use app\api\service\Goods as GoodsService;
use app\api\service\Token as TokenService;
use app\api\validate\GoodsNew;
use app\api\validate\IDMustBePostiveInt;
use app\api\validate\PagingParameter;
use app\api\validate\PictureNew;
use app\lib\exception\GoodsException;
use app\lib\exception\ParameterException;
use app\lib\exception\SuccessMessage;
use think\Cache;
use think\Exception;

class Goods extends BaseController
{
    public function uploadImg()
    {
        $validate = new PictureNew();
        $request = $validate->goCheck();
        //根据Token来获取uid
        $uid = TokenService::getCurrentUid();
        $pic = $request->file();

        $picObject = $pic['file'];
        $picType = $request->param('pic_type');

        //图片原始名称
        $originInfo = $picObject->getInfo();
        //验证上传文件是否为图片格式
        $goods = new GoodsService($uid);
        $result = $goods->checkIsImg($originInfo);
        if ($result) {
            $info = $picObject->rule('uniqid')->move(ROOT_PATH.'public_html'.DS.'tmp_pic');
            if ($info) {
                $dataArray = [
                    'user_id' => $uid,
                    'img_url' => $info->getSaveName(),
                    'pic_type' => $picType,
                    'origin_name' => $originInfo['name'],
                ];
                TmpPicModel::create($dataArray);
                return json(['error_code'=>'ok', 'pic'=>$info->getSaveName(), 'type'=>$picType]);
            } else {
                throw new Exception($info->getError());
            }
        } else {
            throw new ParameterException([
                'msg' => '上传文件格式有误',
            ]);
        }
    }

    public function deleteTmpPic($name = '')
    {
        if (empty(trim($name))) {
            throw new ParameterException();
        }
        $uid = TokenService::getCurrentUid();
        $goods = new GoodsService($uid);
        $goods->delTmpPic($name);
        throw new SuccessMessage();
    }


    public function addGoods()
    {
        $validate = new GoodsNew();
        $request = $validate->goCheck();
        $uid = TokenService::getCurrentUid();
        $dataArray = $validate->getDataByRule($request->post());
        $goods = new GoodsService($uid);
        $goods->add($dataArray);
        throw new SuccessMessage();
    }

    public function getAllGoodsByUser($page = 1, $size = 10)
    {
        (new PagingParameter())->goCheck();
        $uid = TokenService::getCurrentUid();
        $pagingGoods = GoodsModel::getAllByUser($uid, $page, $size);
        if ($pagingGoods->isEmpty()) {
            throw new GoodsException([
                'msg' => '商品已见底线',
                'errorCode' => 30003
            ]);
        }
        $data = $pagingGoods->visible(['id', 'start_time','end_time','goods_name', 'current_price', 'main_img', 'check_status', 'goods_status'])->toArray();
        return json([
            'error_code' => 'ok',
            'data' => $data,
            'current_page' => $pagingGoods->getCurrentPage(),
        ]);
    }

    public function searchGoodsByName($name, $page = 1, $size = 10)
    {
        $pagingGoods = GoodsModel::getGoodsByName($name, $page, $size);
        if ($pagingGoods->isEmpty()) {
            throw new GoodsException([
                'msg' => '商品已见底线',
                'errorCode' => 30003
            ]);
        }
        $data = $pagingGoods->visible(['id', 'start_time','end_time','goods_name', 'current_price', 'main_img', 'goods_status'])->toArray();
        return json([
            'error_code' => 'ok',
            'data' => $data,
            'current_page' => $pagingGoods->getCurrentPage(),
        ]);
    }

    public function getAllGoods($page = 1, $size = 10)
    {
        $pagingGoods = GoodsModel::getAll($page, $size);
        if ($pagingGoods->isEmpty()) {
            throw new GoodsException([
                'msg' => '商品已见底线',
                'errorCode' => 30003
            ]);
        }
        $data = $pagingGoods->visible(['id', 'start_time','end_time','goods_name', 'current_price', 'main_img', 'goods_status'])->toArray();
        return json([
            'error_code' => 'ok',
            'data' => $data,
            'current_page' => $pagingGoods->getCurrentPage(),
        ]);
    }

    public function getOneDetail($id = '')
    {
        $request = (new IDMustBePostiveInt())->goCheck();
        $ip = $request->ip();
        $token = $request->header('token');

        //增加浏览次数
        $hits = GoodsHitsModel::checkIp($id, $ip);
        if (!$hits) {
            GoodsHitsModel::create(['goods_id' => $id, 'ip' => $ip]);
        }

        if (!empty(trim($token))) {
            $vars = Cache::get($token);
            if(!$vars) {
                $goods = GoodsModel::getGoodsDetail($id);
            } else {
                if (!is_array($vars)) {
                    $vars = json_decode($vars, true);
                }
                if (array_key_exists('uid', $vars)) {
                    $uid = $vars['uid'];
                    $goods = GoodsModel::getGoodsDetail($id, $uid);
                } else {
                    $goods = GoodsModel::getGoodsDetail($id);
                }
            }
        } else {
            $goods = GoodsModel::getGoodsDetail($id);
        }

        if (!$goods) {
            throw new GoodsException();
        }
        return json(['error_code'=>'ok', 'goods' =>$goods]);
    }

    public function autoDelTmpPic()
    {
        $delPic = TmpPicModel::getDelPic();
        if (!$delPic->isEmpty()) {
            $picArray = $delPic->toArray();
            $picImgUrl = [];
            foreach ($picArray as $item) {
                unlink(ROOT_PATH.'public_html'.DS.'tmp_pic'.DS.$item['img_url']);
                array_push($picImgUrl, $item['img_url']);
            }
            TmpPicModel::DelTmpPicByImgUrl($picImgUrl);
        }
    }

}