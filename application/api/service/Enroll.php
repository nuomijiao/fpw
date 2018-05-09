<?php
/**
 * Created by Sweet Jiao
 * User: Sweet Jiao
 * Date: 2018/5/9 0009
 * Time: 10:09
 */

namespace app\api\service;

use app\api\model\AuctionEnroll;
use app\lib\enum\PayStatus;
use app\lib\exception\BidException;
use app\lib\exception\GoodsException;
use app\api\model\AuctionEnroll as AuctionEnrollModel;
use app\api\model\Goods as GoodsModel;
use app\api\model\Address as AddressModel;
use app\lib\exception\TokenException;
use app\lib\exception\UserException;
use app\api\service\Token as TokenService;

class Enroll
{
    protected $uid;
    protected $address_id;
    protected $goods_id;

    public function placeEnroll($uid, $goods_id, $address_id)
    {
        $this->address_id = $address_id;
        $this->uid = $uid;
        $this->goods_id = $goods_id;
        //检查商品时间是否已过
        $goods = $this->checkGoods();

        //检查是否交过保证金
        $status = $this->checkIsEnroll();

        if ($status) {
            //开始创建报名订单
            $enrollSnap = $this->snapEnroll($goods);
            $enroll = $this->createEnroll($enrollSnap);
        } else {
            $enroll = [
                'enroll_id' => $status['enroll_id'],
                'enroll_order_no' => $status['enroll_order_no'],
            ];
        }
        return $enroll;
    }

    private function snapEnroll($goods)
    {
        $snap = [
            'enroll_price' => '',
            'snap_name' => '',
            'snap_address' => '',
            'snap_address_mobile' => '',
            'snap_items' => '',
        ];
        $snap['enroll_price'] = $goods->deposit;
        $snap['snap_name'] = $goods->goods_name;
        $snap['snap_address'] = $this->getUserAddress()->address;
        $snap['snap_address_mobile'] = $this->getUserAddress()->mobile;
        $snap['snap_items'] = json($goods);
        return $snap;
    }

    private function createEnroll($snap)
    {
        $auctionEnrollNo = $this->makeOrderNo();
        $enroll = new AuctionEnrollModel();
        $enroll->user_id = $this->uid;
        $enroll->goods_id = $this->goods_id;
        $enroll->enroll_price = $snap['enroll_price'];
        $enroll->snap_name = $snap['snap_name'];
        $enroll->snap_address = $snap['snap_address'];
        $enroll->snap_address_mobile = $snap['snap_address_mobile'];
        $enroll->snap_items = $snap['snap_items'];
        $enroll->pay_status = PayStatus::UNPAYALL;
        $enroll->enroll_order_no = $auctionEnrollNo;
        $enroll->save();
        $enrollID = $enroll->id;
        return [
            'enroll_id' => $enrollID,
            'enroll_order_no' => $auctionEnrollNo,
        ];
    }

    private function checkGoods()
    {
        $goods = GoodsModel::get($this->goods_id);
        if (!$goods) {
            throw new GoodsException();
        }
        //检查商品是否已过时间
        if (time() > $goods->end_time) {
            throw new BidException([
                'msg' => '商品拍卖时间已过',
                'errorCode' => 50002,
            ]);
        }
        return $goods->hidden(['id', 'user_id', 'check_status']);
    }

    private function checkIsEnroll()
    {
        $status = [
            'pass' => true,
            'enroll_id' => '',
            'enroll_order_no' => '',
        ];
        $enroll = AuctionEnrollModel::isPayEnroll($this->uid, $this->goods_id);
        if (!$enroll) {
            $status['pass'] = true;
        } else if ($enroll->pay_status == PayStatus::PAYDEPOSIT) {
            throw new BidException([
                'msg' => '已经交过保证金',
                'errorCode' => 50004,
            ]);
        } else if ($enroll->pay_status == PayStatus::UNPAYALL) {
            $status['pass'] = false;
            $status['enroll_id'] = $enroll->id;
            $status['enroll_order_no'] = $enroll->enroll_order_no;
        } else if ($enroll->pay_status > PayStatus::PAYDEPOSIT) {
            throw new BidException([
                'msg' => '商品拍卖时间已过',
                'errorCode' => 50002,
            ]);
        }
        return $status;
    }

    private function getUserAddress()
    {
        $address = AddressModel::get($this->address_id);
        if (!$address) {
            throw new UserException([
                'msg' => '用户地址不存在',
                'errorCode' => 40002,
            ]);
        }
        $user_id = TokenService::isValidOperate($address->user_id);
        if (!$user_id) {
            throw new TokenException([
                'msg' => '地址与用户不匹配',
                'errorCode' => 10003,
            ]);
        }
        $finalAddress = $address;
        $finalAddress['address'] = $address['province'].$address['city'].$address['district'].$address['detail'];
        return $finalAddress;
    }

    private function makeOrderNo()
    {
        $yCode = array('A', 'B', 'C', 'E', 'F', 'H', 'K', 'M', 'N', 'R');
        $orderSn = $yCode[intval(date('Y')) - 2018] . strtoupper(dechex(date('m'))) . date(
                'd') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf(
                '%02d', rand(0, 99));
        return $orderSn;
    }
}