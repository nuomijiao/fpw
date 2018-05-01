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
                return json(['errorCode'=>'ok', 'pic'=>$info->getSaveName(), 'type'=>$picType]);
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

}