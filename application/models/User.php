<?php

class UserModel
{
    public $errno = 0;
    public $errmsg = "";
    private $_db = null;

    public function __construct()
    {
        $this->_db = new PDO("mysql:host=127.0.0.1;dbname=yaf_api;", "root", "root");
    }

    public function register($uname, $pwd)
    {
        $query = $this->_db->prepare("select count(*) as c from `user` WHERE `name`= ? ");
        $query->execute([$uname]);
        $count = $query->fetchAll();
        // 检查用户是否存在
        if ($count[0]['c'] != 0) {
            $this->errno = -1005;
            $this->errmsg = "用户已存在";
            return false;
        }
        if (strlen($pwd) < 8) {
            $this->errno = -1006;
            $this->errmsg = "密码至少为8位";
            return false;
        } else {
            $password = Common_Password::pwdEncode($pwd);
        }

        $query = $this->_db->prepare("insert into `user`(`name`,`pwd`,`reg_time`) VALUES (?,?,?) ");
        $ret = $query->execute([$uname, $password, date("Y-m-d H:i:s")]);
        if (!$ret) {
            $this->errno = -1006;
            $this->errmsg = "注册失败，写入数据失败";
            return false;
        }
        return true;
    }

    public function login($username, $pwd)
    {
        $query = $this->_db->prepare("select `pwd`,`id` from  `user` WHERE `name`=? ");
        $query->execute([$username]);
        $ret = $query->fetchAll();
        if (!$ret || count($ret) != 1) {
            $this->errno = -1003;
            $this->errmsg = "用户查找失败";
            return false;
        }
        $userInfo = $ret[0];
        if (Common_Password::pwdEncode($pwd) != $userInfo['pwd']) {
            $this->errno = -1004;
            $this->errmsg = "密码错误";
            return false;
        }
        return intval($userInfo[1]);
    }

}
