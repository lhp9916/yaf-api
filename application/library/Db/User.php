<?php

class Db_User extends Db_Base
{
    public function find($username)
    {
        $query = self::getDb()->prepare("select `pwd`,`id` from  `user` WHERE `name`=? ");
        $query->execute([$username]);
        $ret = $query->fetchAll();
        if (!$ret || count($ret) != 1) {
            list(self::$errno, self::$errmsg) = Err_Map::get(1003);
            return false;
        }
        return $ret[0];
    }

    /**
     * 检查用户是否存在
     * @param $uname
     * @return bool
     */
    public function checkExist($uname)
    {
        $query = self::getDb()->prepare("select count(*) as c from `user` WHERE `name`= ? ");
        $query->execute([$uname]);
        $count = $query->fetchAll();
        if ($count[0]['c'] != 0) {
            list(self::$errno, self::$errmsg) = Err_Map::get(1005);
            return false;
        }
        return true;
    }

    /**
     * @param $uname
     * @param $password
     * @return bool
     */
    public function addUser($uname, $password)
    {
        $query = self::getDb()->prepare("insert into `user`(`name`,`pwd`,`reg_time`) VALUES (?,?,?) ");
        $ret = $query->execute([$uname, $password, date("Y-m-d H:i:s")]);
        if (!$ret) {
            list(self::$errno, self::$errmsg) = Err_Map::get(1006);
            return false;
        }
        return true;
    }
}