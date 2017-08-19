<?php

class UserModel
{
    public $errno = 0;
    public $errmsg = "";
    private $_dao = null;

    public function __construct()
    {
        $this->_dao = new Db_User();
    }

    public function register($uname, $pwd)
    {
        $exist = $this->_dao->checkExist($uname);
        if (!$exist) {
            $this->errno = $this->_dao->errno();
            $this->errmsg = $this->_dao->errmsg();
            return false;
        }
        if (strlen($pwd) < 8) {
            list($this->errno, $this->errmsg) = Err_Map::get(1006);
            return false;
        } else {
            $password = Common_Password::pwdEncode($pwd);
        }

        if (!$this->_dao->addUser($uname, $password)) {
            $this->errno = $this->_dao->errno();
            $this->errmsg = $this->_dao->errmsg();
            return false;
        }
        return true;
    }

    public function login($username, $pwd)
    {
        $userInfo = $this->_dao->find($username);
        if (!$userInfo) {
            $this->errno = $this->_dao->errno();
            $this->errmsg = $this->_dao->errmsg();
            return false;
        }
        if (Common_Password::pwdEncode($pwd) != $userInfo['pwd']) {
            list($this->errno, $this->errmsg) = Err_Map::get(1004);
            return false;
        }
        return intval($userInfo[1]);
    }

}
