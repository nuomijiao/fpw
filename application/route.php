<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\Route;

//发送验证码
Route::post('api/:version/sendsms/register', 'api/:version.Sms/registerSms');
//注册用户并获取token
Route::post('api/:version/register', 'api/:version.Register/register');
//登陆获取token
Route::post('api/:version/token/login', 'api/:version.Token/getLoginToken');

//上传商品图片
Route::post('api/:version/goods/upload_img', 'api/:version.Goods/uploadImg');
//删除商品图片
Route::get('api/:version/del_tmp_pic/:name', 'api/:version.Goods/deleteTmpPic');

//上传商品属性
Route::post('api/:version/goods/upload_info', 'api/:version.Goods/addGoods');

