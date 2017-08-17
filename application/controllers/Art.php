<?php

/**
 * @name ArtController
 * @desc 文章控制器
 */
class ArtController extends Yaf_Controller_Abstract
{

    public function indexAction()
    {
        return $this->listAction();
    }

    public function addAction($artId = 0)
    {
        if (!$this->_isAdmin()) {
            echo json_encode([
                "errno" => -2000,
                "errmsg" => "需要管理员权限才能操作"
            ]);
            return false;
        }
        $submit = $this->getRequest()->getQuery("submit", "0");
        if ($submit != "1") {
            echo json_encode(array(
                "errno" => -2001,
                "errmsg" => "请通过正确渠道提交"
            ));
            return false;
        }

        $title = $this->getRequest()->getPost('title', false);
        $contents = $this->getRequest()->getPost('contents', false);
        $author = $this->getRequest()->getPost('author', false);
        $cate = $this->getRequest()->getPost('cate', false);
        if (!$title || !$contents || !$author || !$cate) {
            echo json_encode(array(
                "errno" => -2002,
                "errmsg" => "标题、内容、作者、分类信息不能为空"
            ));
            return false;
        }

        $art = new ArtModel();
        if ($lastId = $art->add(trim($title), trim($contents), trim($author), trim($cate), $artId)) {
            echo json_encode(array(
                "errno" => 0,
                "errmsg" => "",
                "data" => array("lastId" => $lastId)
            ));
        } else {
            echo json_encode(array(
                "errno" => $art->errno,
                "errmsg" => $art->errmsg
            ));
        }
        return false;
    }

    public function editAction()
    {
        if (!$this->_isAdmin()) {
            echo json_encode([
                "errno" => -2000,
                "errmsg" => "需要管理员权限才能操作"
            ]);
            return false;
        }
        $artId = $this->getRequest()->getQuery("artId", "0");
        if (is_numeric($artId) && $artId) {
            return $this->addAction($artId);
        } else {
            echo json_encode([
                "errno" => -2003,
                "errmsg" => "缺少必要的文章ID参数"
            ]);
        }
        return false;
    }

    public function delAction()
    {

    }

    /**
     * 获取文章状态
     */
    public function statusAction()
    {

    }

    /**
     * 文章详细信息
     */
    public function getAction()
    {

    }

    public function listAction()
    {

    }

    private function _isAdmin()
    {
        //todo
        return true;
    }
}
