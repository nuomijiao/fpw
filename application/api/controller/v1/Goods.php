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
use app\api\validate\PictureNew;
use app\lib\exception\ParameterException;
use app\lib\exception\SuccessMessage;
use think\Exception;
use think\Request;

class Goods extends BaseController
{
    public function uploadImg()
    {
        $pic = Request::instance()->file();
        var_dump($pic);die;
//        $request = (new PictureNew())->goCheck('upload');
//        //根据Token来获取uid
//        $uid = TokenService::getCurrentUid();
//        $pic = $request->file('goods_pic');
//        $pic_type = $request->param('pic_type');
//        //图片原始名称
//        $origin_info = $pic->getInfo();
//        $info = $pic->rule('uniqid')->move(ROOT_PATH.'public_html'.DS.'tmp_pic');
//        if ($info) {
//            $dataArray = [
//                'user_id' => $uid,
//                'img_url' => $info->getSaveName(),
//                'pic_type' => $pic_type,
//                'origin_name' => $origin_info['name'],
//            ];
//            TmpPicModel::create($dataArray);
//            return json(['errorCode'=>'ok', 'pic'=>$info->getSaveName(), 'type'=>$pic_type]);
//        } else {
//            throw new Exception($info->getError());
//        }
    }

    public function deleteTmpPic($name)
    {
        if (empty(trim($name))) {
            throw new ParameterException();
        }
        $uid = TokenService::getCurrentUid();
        $goods = new GoodsService();
        $goods->delTmpPic($uid, $name);
        throw new SuccessMessage();
    }

    public function addGoods()
    {
        $validate = new GoodsNew();
        $request = $validate->goCheck();
        $uid = TokenService::getCurrentUid();
        $dataArray = $validate->getDataByRule($request->post());
        $goods = new GoodsService();
        $goods->add($dataArray, $uid);
        throw new SuccessMessage();
    }

}