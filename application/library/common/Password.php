<?php

class Common_Password
{
    const SALT = "salt";

    public static function pwdEncode($pwd)
    {
        return md5(self::SALT . $pwd);
    }
}