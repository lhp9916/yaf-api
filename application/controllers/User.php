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
        $submit = $this->getRequest()->getQuery("submit", "0");
        if ($submit != "1") {
            echo json_encode(array(
                "errno" => -1001,
                "errmsg" => "请通过正确渠道提交"
            ));
            return false;
        }

        //获取参数
        $uname = $this->getRequest()->getPost('uname', false);
        $pwd = $this->getRequest()->getPost('pwd', false);
        if (!$uname || !$pwd) {
            echo json_encode(array(
                "errno" => -1002,
                "errmsg" => "用户名与密码必须传递"
            ));
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

            echo json_encode(array(
                "errno" => 0,
                "errmsg" => "",
                "data" => array("name" => $uname)
            ));

        } else {
            echo json_encode(array(
                "errno" => $model->errno,
                "errmsg" => $model->errmsg
            ));
        }
        return false;
    }

    /**
     * 注册
     */
    public function registerAction()
    {
        //获取参数
        $uname = $this->getRequest()->getPost('uname', false);
        $pwd = $this->getRequest()->getPost('pwd', false);
        if (!$uname || !$pwd) {
            echo json_encode(array(
                "errno" => -1002,
                "errmsg" => "用户名与密码必须传递"
            ));
            return false;
        }

        //调用Model
        $user = new UserModel();
        if ($user->register(trim($uname), trim($pwd))) {
            echo json_encode(array(
                "errno" => 0,
                "errmsg" => "",
                "data" => array("name" => $uname)
            ));
        } else {
            echo json_encode(array(
                "errno" => $user->errno,
                "errmsg" => $user->errmsg
            ));
        }
        return false;
    }
}
