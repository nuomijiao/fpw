<?php
/**
 * Created by Sweet Jiao
 * User: Sweet Jiao
 * Date: 2018/4/26 0026
 * Time: 10:18
 */

namespace app\api\controller\v1;

use app\api\controller\BaseController;
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
use think\Exception;
use app\api\model\Goods as GoodsModel;

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

    public function deleteTmpPic($name)
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
        $data = $pagingGoods->visible(['id', 'start_time','end_time','goods_name', 'current_price', 'main_img'])->toArray();
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
        $data = $pagingGoods->visible(['start_time','end_time','goods_name', 'current_price', 'main_img'])->toArray();
        return json([
            'error_code' => 'ok',
            'data' => $data,
            'current_page' => $pagingGoods->getCurrentPage(),
        ]);
    }

    public function getOneDetail($id = '')
    {
        (new IDMustBePostiveInt())->goCheck();
        $goods = GoodsModel::getGoodsDetail($id);
        if (!$goods) {
            throw new GoodsException();
        }
        return json(['error_code'=>'ok', 'goods' =>$goods]);
    }

}