<?php

class Common_Request
{

    public static function request($key, $default = null)
    {
        $result = isset($_REQUEST[$key]) ? $_REQUEST[$key] : null;
        if ($default != null && $result == null) {
            $result = $default;
        }
        return $result;
    }

    public static function getRequest($key, $default = null)
    {
        return self::request($key, $default);
    }

    public static function postRequest($key, $default = null)
    {
        return self::request($key, $default);
    }

    public static function responce($errno, $errmsg, $data = null)
    {
        $rep = [
            'errno' => $errno,
            'errmsg' => $errmsg
        ];
        if ($data != null) {
            $rep['data'] = $data;
        }
        return json_encode($rep);
    }
}