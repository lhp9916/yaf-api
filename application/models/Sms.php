<?php

class SmsModel
{
    public $errno = 0;
    public $errmsg = "";
    private $_db = null;

    //接口账号
    private $smsUid = '';
    //登录密码
    private $smsPwd = '';
    private $sms = null;

    public function __construct()
    {
        $this->_db = new PDO("mysql:host=127.0.0.1;dbname=yaf_api;", "root", "root");
        $this->sms = new ThirdParty_Sms($this->smsUid, $this->smsPwd);
    }

    public function send($uid)
    {
        $query = $this->_db->prepare("select `mobile` from  `user` WHERE `id`=? ");
        $query->execute([$uid]);
        $ret = $query->fetchAll();
        if (!$ret || count($ret) != 1) {
            $this->errno = -4003;
            $this->errmsg = "用户手机号查找失败";
            return false;
        }
        $mobile = $ret[0]['mobile'];
        if (!$mobile || !is_numeric($mobile) || strlen($mobile) != 11) {
            $this->errno = -4004;
            $this->errmsg = "手机号格式不正确，手机号为：" . (!$mobile ? "空" : $mobile);
            return false;
        }


        //短信内容参数
        $contentParam = array(
            'code' => rand(1000, 9999),
        );

        //变量模板ID
        $template = '100001';

        //发送变量模板短信
        $result = $this->sms->send($mobile, $contentParam, $template);

        if ($result['stat'] == '100') {
            return true;
        } else {
            $this->errno = -4005;
            $this->errmsg = "发送失败：" . $result['stat'] . '(' . $result['message'] . ')';
            return true;
        }
    }

}
