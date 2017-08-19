<?php

/**
 * @name UserController
 * @desc 用户控制器
 */
class UserController extends Yaf_Controller_Abstract
{

    public function indexAction()
    {
        return $this->loginAction();
    }

    public function loginAction()
    {
        $submit = Common_Request::getRequest("submit", '0');
        if ($submit != "1") {
            echo json_encode(Err_Map::get(1001));
            return false;
        }

        //获取参数
        $uname = Common_Request::postRequest('uname', false);
        $pwd = Common_Request::postRequest('pwd', false);
        if (!$uname || !$pwd) {
            echo json_encode(Err_Map::get(1002));
            return false;
        }

        $model = new UserModel();
        $uid = $model->login($uname, $pwd);
        if ($uid) {
            //种session
            session_start();
            $_SESSION['user_token'] = md5('salt' . $_SERVER['REQUEST_TIME'] . $uid);
            $_SESSION['user_token_time'] = $_SERVER['REQUEST_TIME'];
            $_SESSION['user_id'] = $uid;

            echo Common_Request::responce(0, "", array("name" => $uname));

        } else {
            echo Common_Request::responce($model->errno, $model->errmsg);
        }
        return false;
    }

    /**
     * 注册
     */
    public function registerAction()
    {
        //获取参数
        $uname = Common_Request::postRequest('uname', false);
        $pwd = Common_Request::postRequest('pwd', false);
        if (!$uname || !$pwd) {
            echo json_encode(Err_Map::get(1002));
            return false;
        }

        //调用Model
        $user = new UserModel();
        if ($user->register(trim($uname), trim($pwd))) {
            echo Common_Request::responce(0, "", array("name" => $uname));
        } else {
            echo Common_Request::responce($user->errno, $user->errmsg);
        }
        return false;
    }
}
