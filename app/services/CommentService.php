<?php
/**
 * Created by PhpStorm.
 * User: 14521
 * Date: 2019/12/23
 * Time: 15:59
 */

namespace app\services;


use app\libraries\Page;
use app\models\Base;
use app\models\Comment;
use PDO;

class CommentService extends BaseService
{
    /**
     * 添加评论
     * @param array $data
     * @return bool|array
     */
    public function add(array $data)
    {
        if (count($data) === 0) {
            return false;
        }

        $data['ctime'] = date('Y-m-d H:i:s');
        try {
            $pros = Base::getModelProNames(Comment::class);

            $values = array();
            foreach ($data as $key => $val) {
                if (in_array($key, $pros)) {
                    $values[$key] = $val;
                }
            }

            if (count($values) === 0) {
                return false;
            }

            $sql = "insert into comment(" . implode(',', array_keys($values)) . ") values (" . implode(',', array_fill(0, count($values), '?')) . ")";
            $b = $this->db->execute($sql, array_values($values));
            if ($b === false) {
                $this->db->rollback();
                return false;
            }

            $id = $this->db->lastInsertId();
            $comment = $this->getOne($id);
            return $comment;

        } catch (\ReflectionException $e) {
            return false;
        }
    }

    public function getOne(int $id)
    {
        $sql = "select com.id,com.content,com.status,u.nickname,u.photo from comment com left join user u on com.user_id=u.id where com.id=?";
        $rs = $this->db->query($sql, [$id]);
        $rs->setFetchMode(PDO::FETCH_ASSOC);
        $comment = $rs->fetchArray();
        $comment['photo'] = UserService::getPhoto($comment['photo']);
        return $comment;
    }

    /**
     * 修改评论
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data)
    {
        try {
            if (count($data) === 0) {
                return true;
            }

            $pros = Base::getModelProNames(Comment::class);

            $set = array();
            foreach ($data as $key => $val) {
                if (in_array($key, $pros)) {
                    $set[$key] = $val;
                }
            }

            if (count($set) === 0) {
                return true;
            }

            $i = 0;
            $s = '';
            foreach ($set as $key => $val) {
                if ($i > 0) {
                    $s .= ',';
                }
                $s .= ($key . '=?');
            }

            $sql = "update comment set {$s} where id=?";
            $bind = array_values($s);
            $bind[] = $id;
            $b = $this->db->execute($sql, $bind);

            if ($b === false) {
                return false;
            } else {
                return true;
            }
        } catch (\ReflectionException $e) {
            return false;
        }
    }

    /**
     * 获取用户评论
     * @param Page $page
     * @param int $userId
     * @return Page|bool
     */
    public function listOfUser(Page $page, int $userId)
    {
        if ($userId <= 0) {
            return false;
        }

        $index = $page->getIndex();
        $rows = $page->getPageSize();
        $sql = "select id,content,user_id,article_id,ctime,pid from comment where user_id=? and status = 1 order by id desc limit {$index},{$rows}";
        $rs = $this->db->query($sql, [$userId]);
        $rs->setFetchMode(PDO::FETCH_ASSOC);
        $comments = $rs->fetchAll();
        $page->setItems($comments);

        $sql = "select count(id) from comment where user_id=? and status=1";
        $rs = $this->db->query($sql, [$userId]);
        $rs->setFetchMode(PDO::FETCH_NUM);
        $totalRes = $rs->fetchArray();
        $page->setTotalItems($totalRes[0]);

        return $page;
    }

    /**
     * 获取文章评论
     * @param Page $page
     * @param int $articleId
     * @return Page|bool
     */
    public function listOfArticle(Page $page, int $articleId)
    {
        if ($articleId <= 0) {
            return false;
        }

        $index = $page->getIndex();
        $rows = $page->getPageSize();
        $sql = "select 
com.id,com.content,com.user_id,com.article_id,com.ctime,com.status,u.photo,u.nickname
from comment com left join user u on com.user_id=u.id  
 where com.article_id=? and com.status = 1 order by id desc limit {$index},{$rows}";
        $rs = $this->db->query($sql, [$articleId]);
        $rs->setFetchMode(PDO::FETCH_ASSOC);
        $comments = $rs->fetchAll();

        foreach ($comments as &$comment) {
            $comment['photo'] = UserService::getPhoto($comment['photo']);
        }

        $page->setItems($comments);

        $sql = "select count(id) from comment where article_id=? and status=1";
        $rs = $this->db->query($sql, [$articleId]);
        $rs->setFetchMode(PDO::FETCH_NUM);
        $totalRes = $rs->fetchArray();
        $page->setTotalItems($totalRes[0]);

        return $page;
    }

    /**
     * 获取评论的评论
     * @param Page $page
     * @param int $pid
     * @return Page|bool
     */
    public function listOfComment(Page $page, int $pid)
    {
        if ($pid <= 0) {
            return false;
        }

        $index = $page->getIndex();
        $rows = $page->getPageSize();
        $sql = "select id,content,user_id,article_id,ctime,pid from comment where pid=? and status = 1 order by id desc limit {$index},{$rows}";
        $rs = $this->db->query($sql, [$pid]);
        $rs->setFetchMode(PDO::FETCH_ASSOC);
        $comments = $rs->fetchAll();
        $page->setItems($comments);

        $sql = "select count(id) from comment where pid=? and status=1";
        $rs = $this->db->query($sql, [$pid]);
        $rs->setFetchMode(PDO::FETCH_NUM);
        $totalRes = $rs->fetchArray();
        $page->setTotalItems($totalRes[0]);

        return $page;
    }

    /**
     * 屏蔽评论
     * @param int $id
     * @return bool
     */
    public function hide(int $id)
    {
        $sql = "update comment set status=2 where id=?";
        $b = $this->db->execute($sql, [$id]);
        return $b === false ? false : true;
    }
}