<?php

class IpModel
{
    public $errno = 0;
    public $errmsg = "";

    public function get($ip)
    {
        $ret = ThirdParty_Ip::find($ip);
        return $ret;
    }

}
