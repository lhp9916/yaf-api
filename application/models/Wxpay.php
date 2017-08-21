<?php

$wxpayLibPath = dirname(__FILE__) . '/../library/ThirdParty/Wxpay/';
include_once($wxpayLibPath . 'WxPay.Api.php');
include_once($wxpayLibPath . 'WxPay.Notify.php');
include_once($wxpayLibPath . 'WxPay.NativePay.php');
include_once($wxpayLibPath . 'WxPay.Data.php');

class WxpayModel extends WxPayNotify
{
    public $errno = 0;
    public $errmsg = "";

    private $_db = null;

    public function __construct()
    {
        $this->_db = new PDO("mysql:host=127.0.0.1;dbname=yaf_api;", "root", "root");
    }

    public function createBill($itemId, $uid)
    {
        $query = $this->_db->prepare("select * from `item` WHERE `id`=? ");
        $query->execute([$itemId]);
        $ret = $query->fetchAll();
        if (!$ret || count($ret) != 1) {
            $this->errno = -6003;
            $this->errmsg = "找不到这件商品";
            return false;
        }
        $item = $ret[0];
        //判断商品是否过期
        if (strtotime($item['etime']) <= time()) {
            $this->errno = -6004;
            $this->errmsg = "商品已过期，不能购买";
            return false;
        }
        //判断库存
        if ($item['stock'] <= 0) {
            $this->errno = -6005;
            $this->errmsg = "库存不足，不能购买";
            return false;
        }

        try {
            //开启事务
            $this->_db->beginTransaction();

            $query = $this->_db->prepare("insert into `bill` (`itemid`,`uid`,`price`,`status`) VALUES (?,?,?,'unpaid')");
            $ret = $query->execute([$itemId, $uid, intval($item['price'])]);
            if (!$ret) {
                $this->errno = -6006;
                $this->errmsg = "创建订单失败";
                return false;
            }
            $lastId = intval($this->_db->lastInsertId());

            $query = $this->_db->prepare("update `item` set `stock`=`stock`-1 WHERE `id`=? ");
            $ret = $query->execute([$itemId]);
            if (!$ret) {
                $this->errno = -6007;
                $this->errmsg = "更新库存失败";
                return false;
            }

            $this->_db->commit();

            return $lastId;
        } catch (PDOException $e) {
            $this->_db->rollBack();
            $this->errno = -6008;
            $this->errmsg = "数据库发生错误";
            return false;
        }
    }

    public function qrcode($billId)
    {
        //找订单
        $query = $this->_db->prepare("select * from `bill` WHERE `id`=? ");
        $query->execute([$billId]);
        $ret = $query->fetchAll();
        if (!$ret || count($ret) != 1) {
            $this->errno = -6009;
            $this->errmsg = "找不到账单信息";
            return false;
        }
        $bill = $ret[0];

        //找商品
        $query = $this->_db->prepare("select * from `item` WHERE `id`=? ");
        $query->execute([$bill['itemid']]);
        $ret = $query->fetchAll();
        if (!$ret || count($ret) != 1) {
            $this->errno = -6010;
            $this->errmsg = "找不到这件商品";
            return false;
        }
        $item = $ret[0];

        /**
         * 调用微信支付lib，生成账单二维码
         */
        $input = new WxPayUnifiedOrder();
        $input->SetBody($item['name']);
        $input->SetAttach($billId);
        $input->SetOut_trade_no(WxPayConfig::MCHID . date("YmdHis"));
        $input->SetTotal_fee($bill['price']);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 86400 * 3));
        $input->SetGoods_tag($item['name']);
        //回调接口
        $input->SetNotify_url("http://192.168.1.3/wxpay/callback");
        $input->SetTrade_type("NATIVE");
        $input->SetProduct_id($billId);

        $notify = new NativePay();
        $result = $notify->GetPayUrl($input);
        $url = $result["code_url"];
        return $url;

    }

    public function callback()
    {
        /**
         * 订单成功，更新账单
         * TODO 因为SK没有，没法与微信支付的服务端做Response确认，只能单方面记账
         */
        $xmlData = file_get_contents("php://input");
        if (substr_count($xmlData, "<result_code><![CDATA[SUCCESS]]></result_code>") == 1 &&
            substr_count($xmlData, "<return_code><![CDATA[SUCCESS]]></return_code>") == 1) {
            preg_match('/<attach>(.*)\[(\d+)\](.*)<\/attach>/i', $xmlData, $match);
            if (isset($match[2]) && is_numeric($match[2])) {
                $billId = intval($match[2]);
            }
            preg_match('/<transaction_id>(.*)\[(\d+)\](.*)<\/transaction_id>/i', $xmlData, $match);
            if (isset($match[2]) && is_numeric($match[2])) {
                $transactionId = intval($match[2]);
            }
        }

        if (isset($billId) && isset($transactionId)) {
            $query = $this->_db->prepare("update `bill` set `transaction`=?,`ptime`=?,`status`='paid' WHERE `id`=? ");
            $query->execute([$transactionId, date('Y-m-d H:i:s'), $billId]);
        }
    }

}
