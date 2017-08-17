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

    /**
     * 删除文章(删除数据)
     * @param $artId
     * @return bool
     */
    public function del($artId)
    {
        $query = $this->_db->prepare("delete from `art` WHERE `id`=? ");
        $ret = $query->execute([intval($artId)]);
        if (!$ret) {
            $this->errno = -2007;
            $this->errmsg = "删除失败，errInfo:" . end($query->errorInfo());
            return false;
        }
        return true;
    }

    /**
     * @param $artId
     * @param string $status
     * delete 删除
     * online 上线
     * offline 未上线
     * @return bool
     */
    public function status($artId, $status = 'offline')
    {
        $query = $this->_db->prepare("update `art` set `status`=?,`mtime`=? WHERE `id`=? ");
        $ret = $query->execute([$status, date("Y-m-d H:i:s"), intval($artId)]);
        if (!$ret) {
            $this->errno = -2008;
            $this->errmsg = "更新文章状态失败，errInfo:" . end($query->errorInfo());
            return false;
        }
        return true;
    }

    public function get($artId)
    {
        $query = $this->_db->prepare("select `title`,`contents`,`author`,`cate`,`ctime`,`mtime`,`status` from `art` WHERE `id`=? ");
        $status = $query->execute([intval($artId)]);
        $ret = $query->fetchAll();
        if (!$status || !$ret) {
            $this->errno = -2009;
            $this->errmsg = "查询失败，errInfo:" . end($query->errorInfo());
            return false;
        }
        $artInfo = $ret[0];

        //获取分类信息
        $query = $this->_db->prepare("select `name` from `cate` WHERE `id`=? ");
        $query->execute([$artInfo['cate']]);
        $ret = $query->fetchAll();
        if (!$ret) {
            $this->errno = -2010;
            $this->errmsg = "获取分类信息失败，errInfo:" . end($query->errorInfo());
            return false;
        }
        $artInfo['cateName'] = $ret[0]['name'];
        $data = [
            'id' => intval($artId),
            'title' => $artInfo['title'],
            'contents' => $artInfo['contents'],
            'author' => $artInfo['author'],
            'cateName' => $artInfo['cateName'],
            'cateId' => intval($artInfo['cate']),
            'ctime' => $artInfo['ctime'],
            'mtime' => $artInfo['mtime'],
            'status' => $artInfo['status']
        ];
        return $data;
    }

    public function list($pageNo = 0, $pageSize = 10, $cate = 0, $status = 'online')
    {
        $start = $pageNo * $pageSize + ($pageNo == 0 ? 0 : 1);
        if ($cate == 0) {
            $filter = [$status, intval($start), intval($pageSize)];
            $query = $this->_db->prepare("select `id`,`title`,`contents`,`author`,`cate`,`ctime`,`mtime`,`status` from `art` WHERE `status`=? ORDER BY `ctime` DESC limit ?,? ");
        } else {
            $filter = [intval($cate), $status, intval($start), intval($pageSize)];
            $query = $this->_db->prepare("select `id`,`title`,`contents`,`author`,`cate`,`ctime`,`mtime`,`status` from `art` WHERE `cate`=? AND `status`=? ORDER BY `ctime` DESC limit ?,? ");
        }
        $query->execute($filter);
        $ret = $query->fetchAll();
        if (!$ret) {
            $this->errno = -2011;
            $this->errmsg = "获取文章列表失败，errInfo:" . end($query->errorInfo());
            return false;
        }

        $data = array();
        $cateInfo = array();
        foreach ($ret as $item) {
            if (isset($cateInfo[$item['cate']])) {
                $cateName = $cateInfo[$item['cate']];
            } else {
                $query = $this->_db->prepare("select `name` from `cate` WHERE `id`=? ");
                $query->execute([$item['cate']]);
                $retCat = $query->fetchAll();
                if (!$retCat) {
                    $this->errno = -2010;
                    $this->errmsg = "获取分类信息失败，errInfo:" . end($query->errorInfo());
                    return false;
                }
                $cateName = $cateInfo[$item['cate']] = $retCat[0]['name'];
            }

            //正文太长则切割
            $contents = mb_strlen($item['contents']) > 30 ? mb_substr($item['contents'], 0, 30) : $item['contents'];
            $data[] = [
                'id' => intval($item['id']),
                'title' => $item['title'],
                'contents' => $contents,
                'author' => $item['author'],
                'cate' => $item['cate'],
                'cateName' => $cateName,
                'ctime' => $item['ctime'],
                'mtime' => $item['mtime'],
                'status' => $item['status']
            ];
        }
        return $data;
    }
}
