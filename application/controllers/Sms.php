<?php

/**
 * @name UserController
 * @desc 用户控制器
 */
class SmsController extends Yaf_Controller_Abstract
{
    public function sendAction()
    {
        $submit = $this->getRequest()->getQuery("submit", "0");
        if ($submit != "1") {
            echo json_encode(array(
                "errno" => -1001,
                "errmsg" => "请通过正确渠道提交"
            ));
            return false;
        }

        $uid = $this->getRequest()->getPost("uid", false);
        if (!$uid) {
            echo json_encode(array(
                "errno" => -4001,
                "errmsg" => "用户ID不能为空"
            ));
            return false;
        }

        $model = new SmsModel();
        if ($model->send(intval($uid))) {
            echo json_encode(array(
                "errno" => 0,
                "errmsg" => ""
            ));
        } else {
            echo json_encode(array(
                "errno" => $model->errno,
                "errmsg" => $model->errmsg
            ));
        }
        return false;
    }
}
