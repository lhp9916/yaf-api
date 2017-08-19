<?php

class PushController extends Yaf_Controller_Abstract
{
    public function singleAction()
    {
        $cid = Common_Request::getRequest('cid', "");
        $msg = Common_Request::getRequest('msg', "");
        if (!$cid || !$msg) {
            echo Common_Request::responce(-7002, "推送用户及推送内容不能为空");
            return false;
        }

        $model = new PushModel();
        if ($model->single($cid, $msg)) {
            echo Common_Request::responce(0, "");
        } else {
            echo Common_Request::responce($model->errno, $model->errmsg);
        }
        return false;
    }

    public function toAllAction()
    {
        $msg = Common_Request::getRequest('msg', "");
        if (!$msg) {
            echo Common_Request::responce(-7003, "推送内容不能为空");
            return false;
        }

        $model = new PushModel();
        if ($model->toAll($msg)) {
            echo Common_Request::responce(0, "");
        } else {
            echo Common_Request::responce($model->errno, $model->errmsg);
        }
        return false;
    }
}