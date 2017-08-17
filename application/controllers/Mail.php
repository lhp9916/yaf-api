<?php

/**
 * @name MailController
 * @desc 邮件控制器
 */
class MailController extends Yaf_Controller_Abstract
{
    public function sendAction()
    {
        $submit = $this->getRequest()->getQuery("submit", "0");
        if ($submit != "1") {
            echo json_encode(array(
                "errno" => -3001,
                "errmsg" => "请通过正确渠道提交"
            ));
            return false;
        }

        //获取参数
        $uid = $this->getRequest()->getPost("uid", false);
        $title = $this->getRequest()->getPost("title", false);
        $contents = $this->getRequest()->getPost("contents", false);
        if (!$uid || !$title || !$contents) {
            echo json_encode(array(
                "errno" => -3002,
                "errmsg" => "用户id、邮件标题、邮件内容均不能为空。"
            ));
            return false;
        }

        $model = new MailModel();
        if ($model->send(intval($uid), trim($title), trim($contents))) {
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
