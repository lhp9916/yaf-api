<?php


class Err_Map
{
    private static $_errMap = array(
        /**
         * 1XXX
         * User
         */
        1001 => '请通过正确渠道提交',
        1002 => '用户名与密码必须传递',
        1003 => '用户查找失败',
        1004 => '密码错误',
        1005 => '用户已存在',
        1006 => '注册失败，写入数据失败',
    );

    public static function get($code)
    {
        if (isset(self::$_errMap[$code])) {
            return array('errno' => (0 - $code), 'errmsg' => self::$_errMap[$code]);
        }
        return array('errno' => (0 - $code), 'errmsg' => "undefined this error number");
    }
}