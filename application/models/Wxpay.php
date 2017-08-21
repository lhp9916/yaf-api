<?php

class WxpayModel
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

}
