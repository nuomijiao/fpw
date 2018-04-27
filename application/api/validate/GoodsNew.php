<?php
/**
 * Created by Sweet Jiao
 * User: Sweet Jiao
 * Date: 2018/4/26 0026
 * Time: 14:02
 */

namespace app\api\validate;


class GoodsNew extends BaseValidate
{
    protected $rule = [
        'main_img_url|商品主图' => 'require|array',
        'detail_img_url|详情图片' => 'require|array',
        'goods_number|货号' => 'require|isNotEmpty',
        'goods_name|品名' => 'require|isNotEmpty',
        'category|品类' => '',
        'starting_price|起拍价' => 'require|number',
        'increments_price|竞价幅度' => 'require|number',
        'deposit|保证金' => 'require|number',
        'gg|规格' => 'require|isNotEmpty',
        'gy|工艺' => 'require|isNotEmpty',
        'cf|成分' => 'require|isNotEmpty',
        'mf|门幅' => 'require|isNotEmpty',
        'kz|克重' => 'require|isNotEmpty',
        'zz|组织' => 'require|isNotEmpty',
        'jm|经密' => 'require|isNotEmpty',
        'wm|纬密' => 'require|isNotEmpty',
        'hd|厚度' => '',
        'tl|弹力' => '',
        'rr|柔软' => '',
        'tq|透气' => '',
        'purpose|用途' => '',
        'detail|详情描述' => '',
        'start_time|开始时间' => 'require|isTimeStamp',
        'end_time|结束时间' => 'require|isTimeStamp',
    ];

    protected $message = [
        'main_img_url.array' => '商品主图数据不符要求',
        'detail_img_url.array' => '详情图片数据不符要求',
        'goods_number' => '货号不能为空',
        'goods_name' => '品名不能为空',
        'starting_price.number' => '起拍价数值不符要求',
        'increments_price.number' => '竞价幅度数值不符要求',
        'deposit.number' => '保证金数值不符要求',
        'gg' => '规格不能为空',
        'gy' => '工艺不能为空',
        'cf' => '成分不能为空',
        'mf' => '门幅不能为空',
        'kz' => '克重不能为空',
        'zz' => '组织不能为空',
        'jm' => '经密不能为空',
        'wm' => '纬密不能为空',
    ];
    
    public function isTimeStamp($value)
    {
        if (strtotime(date('m-d-Y H:i:s', $value)) === $value) {
            return true;
        } else {
            return false;
        }
    }
}