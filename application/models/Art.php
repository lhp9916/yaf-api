<?php

/**
 * @name ArtModel
 */
class ArtModel
{
    public $errno = 0;
    public $errmsg = "";
    private $_db = null;

    public function __construct()
    {
        $this->_db = new PDO("mysql:host=127.0.0.1;dbname=yaf_api;", "root", "root");
        //不设置下面这行的话，PDO在拼接sql的时候，把int 0 转成string 0
        $this->_db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }

    //新增和编辑
    public function add($title, $contents, $author, $cate, $artId)
    {
        $isEdit = false;
        if ($artId != 0 && is_numeric($artId)) {
            //编辑文章
            $query = $this->_db->prepare("select COUNT(*) as c from `art` WHERE `id`=? ");
            $query->execute([$artId]);
            $ret = $query->fetchAll();
//            print_r($ret);
            if (!$ret || $ret[0]['c'] != 1) {
                $this->errno = -2004;
                $this->errmsg = "找不到你要编辑的文章";
                return false;
            }
            $isEdit = true;
        } else {
            //新增文章
            //新增需要校验cate,如果是编辑，cate之前创建过就不用在校验
            $query = $this->_db->prepare("select count(*) from `cate` WHERE `id`=? ");
            $query->execute([$cate]);
            $ret = $query->fetchAll();
            if (!$ret || $ret[0][0] == 0) {
                $this->errno = -2005;
                $this->errmsg = "找不到对应ID的分类信息，cate id:" . $cate . ",请先创建该分类。";
                return false;
            }
        }

        //写入数据
        $data = [$title, $contents, $author, intval($cate), date("Y-m-d H:i:s")];
        if (!$isEdit) {
            $query = $this->_db->prepare("insert into `art` (`title`,`contents`,`author`,`cate`,`ctime`) VALUES (?,?,?,?,?) ");
        } else {
            $query = $this->_db->prepare("update `art` set `title`=? ,`contents`=?,`author`=?,`cate`=?,`mtime`=? WHERE  `id`=? ");
            $data[] = $artId;
        }
        $ret = $query->execute($data);
        if (!$ret) {
            $this->errno = -2006;
            $this->errmsg = "操作文章数据表失败，" . end($query->errorInfo());
            return false;
        }

        //返回文章最后的ID值
        if (!$isEdit) {
            return intval($this->_db->lastInsertId());
        } else {
            return intval($artId);
        }
    }

}
