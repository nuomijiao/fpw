<?php
/**
 * Created by Sweet Jiao
 * User: Sweet Jiao
 * Date: 2018/4/26 0026
 * Time: 10:54
 */

namespace app\api\validate;


class PictureNew extends BaseValidate
{
    protected $rule = [
        'file' => 'require|image|fileExt:jpg,png,gif|fileMime:image/jpeg,image/png,image/gif',
        'pic_type' => 'require|in:MainImg,DetailImg',
    ];
    protected $message = [
        'file.require' => '图像文件不能为空',
        'file.image' => '请上传图像文件',
        'file.fileExt' => '上传文件类型不合法',
        'file.fileMime' => '上传文件的Mine类型不合法',
    ];
}