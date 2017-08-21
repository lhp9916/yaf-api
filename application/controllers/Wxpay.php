<?php

/**
 * @name WxpayController
 * @desc 微信支付功能封装
 */

$qrcodeLibPath = dirname(__FILE__) . '/../library/ThirdParty/Qrcode/';
include_once($qrcodeLibPath . 'Qrcode.php');

class WxpayController extends Yaf_Controller_Abstract
{
    //创建订单
    public function createBillAction()
    {
        $itemId = Common_Request::postRequest("itemid", "");
        if (!$itemId) {
            echo Common_Request::responce(-6001, "请传递正确的商品id");
            return false;
        }

        //检查是否登录
        session_start();
        if (!isset($_SESSION['user_token_time']) || !isset($_SESSION['user_token'])
            || !isset($_SESSION['user_id']) || md5("salt" . $_SESSION['user_token_time'] . $_SESSION['user_id']) != $_SESSION['user_token']) {
            echo Common_Request::responce(-60012, "请先登录");
            return false;
        }

        $model = new WxpayModel();
        if ($lastId = $model->createBill($itemId, $_SESSION['user_id'])) {
            echo Common_Request::responce(0, "", ['billId' => $lastId]);
        } else {
            echo Common_Request::responce($model->errno, $model->errmsg);
        }

        return false;
    }

    //生成支付二维码
    public function qrcodeAction()
    {
        $billId = Common_Request::postRequest("billid", "");
        if (!$billId) {
            echo Common_Request::responce(-6008, "请传递正确的订单id");
            return false;
        }

        $model = new WxpayModel();
        if ($data = $model->qrcode($billId)) {
            //输出二维码
            QRcode::png($data);
        } else {
            echo Common_Request::responce($model->errno, $model->errmsg);
        }
        return false;
    }

    //微信支付成功回调接口
    public function callbackAction()
    {

    }

}
