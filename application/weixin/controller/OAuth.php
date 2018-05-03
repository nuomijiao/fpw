<?php
/**
 * Created by Sweet Jiao
 * User: Sweet Jiao
 * Date: 2018/5/3 0003
 * Time: 15:58
 */

namespace app\weixin\controller;


class OAuth extends BaseController
{
    private static $appID;
    private static $appSecret;

    public function __construct()
    {
        self::$appID = config('weixin.appID');
        self::$appSecret = config('weixin.appSecret');
    }

    //生成OAuth2.0的URL
    public static function oAuthAuthorize($redirectUrl, $scope, $state = NULL)
    {
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".self::$appID."&redirect_uri=".$redirectUrl."&response_type=code&scope=".$scope."&state=".$state."#wechat_redirect";
        return $url;
    }

    public static function oAuthAccessToken($code)
    {
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".self::$appID."&secret=".self::$appSecret."&code=".$code."&grant_type=authorization_code";
        $accessToken = self::httpRequest($url);
        return json_decode($accessToken);
    }

    public static function oAuthGetUserInfo($accessToken, $openID)
    {
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$accessToken."&openid=".$openID."&lang=zh_CN";
        $userInfo = self::httpRequest($url);
        return json_decode($userInfo);
    }

}