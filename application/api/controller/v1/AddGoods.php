<?php
/**
 * Created by Sweet Jiao
 * User: Sweet Jiao
 * Date: 2018/4/26 0026
 * Time: 10:18
 */

namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\validate\GoodsNew;
use app\api\validate\PictureNew;
use app\api\model\TmpPic as TmpPicModel;
use app\lib\exception\GoodsException;
use app\api\service\Token as TokenService;
use app\api\model\User as UserModel;
use app\lib\exception\UserException;
use think\Exception;

class AddGoods extends BaseController
{
    public function uploadImg()
    {
        $request = (new PictureNew())->goCheck('upload');
        //根据Token来获取uid
        //根据uid来查找用户数据，判断用户是否存在，如果不存在抛出异常。
        $uid = TokenService::getCurrentUid();
        $user = UserModel::get($uid);
        if (!$user) {
            throw new UserException();
        }
        $pic = $request->file('goods_pic');
        $pic_type = $request->param('pic_type');
        //图片原始名称
        $origin_info = $pic->getInfo();
        $info = $pic->move(ROOT_PATH.'public_html'.DS.'tmp_pic');
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

    public function addGoods()
    {
        $validate = new GoodsNew();
        $request = $validate->goCheck();
        $uid = TokenService::getCurrentUid();
        $user = UserModel::get($uid);
        if (!$user) {
            throw new UserException();
        }
        $dataArray = $validate->getDataByRule($request->post());
    }
}