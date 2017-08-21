<?php

/**
 * @name WxpayController
 * @desc 微信支付功能封装
 */
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

    }

    //微信支付成功回调接口
    public function callbackAction()
    {

    }

}
