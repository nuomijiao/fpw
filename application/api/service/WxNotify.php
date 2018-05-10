<?php
/**
 * Created by Sweet Jiao
 * User: Sweet Jiao
 * Date: 2018/5/10 0010
 * Time: 8:45
 */

namespace app\api\service;


use think\Loader;

Loader::import('WxPay.WxPay', EXTEND_PATH, '.Api.php');

class WxNotify extends \WxPayNotify
{
    private $type;

    function __construct($type = '')
    {
        $this->type = $type;
    }

    public function NotifyProcess($data, &$msg)
    {
        if ($data['result_code'] == 'SUCCESS') {
            $orderNo = $data['out_trade_no'];
            Db::startTrans();
            try {
                $order = OrderModel::where('order_no', '=', $orderNo)->lock(true)->find();
                if ($order->status == 1) {
                    $service = new OrderService();
                    $stockStatus = $service->checkOrderStock($order->id);
                    if ($stockStatus['pass']) {
                        $this->updateOrderStatus($order->id, true);
                        $this->reduceStock($stockStatus);
                    } else {
                        $this->updateOrderStatus($order->id, false);
                    }
                }
                Db::commit();
                return true;
            } catch (Exception $ex) {
                Db::rollback();
                Log::error($ex);
                return false;

            }
        } else {
            return true;
        }
    }
}