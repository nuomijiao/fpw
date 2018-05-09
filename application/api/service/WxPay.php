<?php
/**
 * Created by Sweet Jiao
 * User: Sweet Jiao
 * Date: 2018/5/9 0009
 * Time: 15:30
 */

namespace app\api\service;



use app\lib\enum\PayStatus;
use app\lib\exception\BidException;
use app\lib\exception\TokenException;
use think\Exception;
use think\Loader;
use app\api\model\AuctionEnroll as AuctionEnrollModel;
use app\api\service\Token as TokenService;
use think\Log;

// extend/WxPay/WxPay.Api.php
Loader::import('WxPay.WxPay', EXTEND_PATH, '.Api.php');

class WxPay
{
    private $orderID;
    private $enrollOrderNO;
    private $finalOrderNo;
    private $type;

    function __construct($orderID, $type)
    {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') === false)
        {
            throw new BidException([
                'msg' => '请在微信浏览器环境下支付',
                'errorCode' => 50006,
            ]);
        }
        if (!$orderID) {
            throw new Exception('订单号不允许为NULL');
        }
        $this->orderID = $orderID;
        $this->type = $type;
    }
    public function payEnroll()
    {
        //检查订单和用户是否匹配
        $enroll = $this->checkOrderValid('enroll');
        return $this->makeWxPreOrder($enroll->enroll_price);
    }

    public function makeWxPreOrder($totalPrice)
    {
        //openid
        $openid = TokenService::getCurrentTokenVar('openid');
        if (!$openid) {
            throw new TokenException();
        }

        $wxOrderData = new \WxPayUnifiedOrder();
        if ('enroll' == $this->type) {
            $wxOrderData->SetOut_trade_no($this->enrollOrderNO);
            $wxOrderData->SetNotify_url(config('secure.pay_back_url'));
        } else if ('final' == $this->type) {
            $wxOrderData->SetOut_trade_no($this->finalOrderNo);
            $wxOrderData->SetNotify_url(config('secure.pay_back_url'));
        }
        $wxOrderData->SetTrade_type('JSAPI');
        $wxOrderData->SetTotal_fee($totalPrice * 100);
        $wxOrderData->SetBody('纺拍网');
        $wxOrderData->SetOpenid($openid);
        return $this->getPaySignature($wxOrderData);
    }

    private function getPaySignature($wxOrderData)
    {
        $wxOrder = \WxPayApi::unifiedOrder($wxOrderData);
        if ($wxOrder['return_code'] != 'SUCCESS' || $wxOrder['result_code'] != 'SUCCESS') {
            Log::record($wxOrder, 'error');
            Log::record('获取预支付订单失败', 'error');
        }
        //prepay_id
        if ('enroll' == $this->type) {
            $this->recordEnrollPreOrder($wxOrder);
        } else if ('final' == $this->type) {
            $this->recordFinalPreOrder($wxOrder);
        }

        $signature = $this->sign($wxOrder);
        return $signature;
    }

    private function sign($wxOrder)
    {
        $jsApiPayData = new \WxPayJsApiPay();
        $jsApiPayData->SetAppid(config('wx.appid'));
        $jsApiPayData->SetTimeStamp((string)time());
        $rand = md5(time() . mt_rand(0, 1000));
        $jsApiPayData->SetNonceStr($rand);
        $jsApiPayData->SetPackage('prepay_id='.$wxOrder['prepay_id']);
        $jsApiPayData->SetSignType('md5');

        $sign = $jsApiPayData->MakeSign();
        $rawValues = $jsApiPayData->GetValues();
        $rawValues['paySign'] = $sign;

        unset($rawValues['appId']);

        return $rawValues;
    }

    private function recordEnrollPreOrder($wxOrder)
    {
        AuctionEnrollModel::where('id', '=', $this->orderID)->update(['enroll_prepay_id'=>$wxOrder['prepay_id']]);

    }

    private function recordFinalPreOrder($wxOrder)
    {
        AuctionEnrollModel::where('id', '=', $this->orderID)->update(['final_prepay_id'=>$wxOrder['prepay_id']]);

    }
    private function checkOrderValid($type)
    {
        $enroll = AuctionEnrollModel::where('id', '=', $this->orderID)->find();
        if (!$enroll) {
            throw new BidException([
                'msg' => '该订单不存在',
                'errorCode' => 50005
            ]);
        }
        if (!TokenService::isValidOperate($enroll->id)) {
            throw new TokenException([
                'msg' => '订单与用户不匹配',
                'errorCode' => 10003,
            ]);
        }

        if ('enroll' == $type) {
            if ($enroll->pay_status >= PayStatus::PAYDEPOSIT) {
                throw new BidException([
                    'msg' => '订单保证金已支付过啦',
                    'errorCode' => 50004,
                ]);
            }
        } else if ('final' == $type) {
            if ($enroll->pay_status >= PayStatus::PAYALL) {
                throw new BidException([
                    'msg' => '订单尾款已支付过啦',
                    'errorCode' => 50004,
                ]);
            }
        }

        $this->enrollOrderNO = $enroll->enroll_order_no;
        $this->finalOrderNo = $enroll->final_order_no;
        return $enroll;
    }
}