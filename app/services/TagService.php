<?php
/**
 * Created by PhpStorm.
 * User: 14521
 * Date: 2019/11/4
 * Time: 13:45
 */

namespace app\services;

use app\models\Tag;

class TagService extends BaseService
{

    public function exist($tagName)
    {
        $count = Tag::count(['name=?0', 'bind' => [$tagName]]);
        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取tag总数指定文章
     * @param $articleId
     * @return mixed
     */
    public function getArticleTagCountByAid($articleId)
    {
        $sql = "select count(`id`) total from `article_tag` where `article_id`=?";
        $rs = $this->db->query($sql, [$articleId]);
        $rs->setFetchMode(\PDO::FETCH_ASSOC);
        $res = $rs->fetchArray();
        return $res['total'];
    }

    /**
     * 获取tag列表指定文章
     * @param $articleId
     * @return array
     */
    public function getArticleTagsByAid($articleId)
    {
        $sql = "select `id`,`article_id`,`tag_id` from `article_tag` where `article_id`=?";
        $rs = $this->db->query($sql, [$articleId]);
        $rs->setFetchMode(\PDO::FETCH_ASSOC);
        $res = $rs->fetchAll();
        return $res;
    }

    /**
     * 软删除tag指定文章
     * @param $articleId
     * @return array|bool
     */
    public function deleteArticleTagSoft($articleId)
    {
        $ids = [];
        $total = $this->getArticleTagCountByAid($articleId);

        if ($total > 0) {
            $sql = "update `article_tag` set `tag_id`=0 where `article_id`=?";
            $b = $this->db->execute($sql, [$articleId]);
            if (false === $b) {
                return false;
            }

            $res = $this->getArticleTagsByAid($articleId);
            foreach ($res as $val) {
                $ids[] = $val['id'];
            }
        }

        return $ids;
    }

    /**
     * 保持文章tag
     * @param $tagNames
     * @param $articleId
     * @return bool
     */
    public function saveArticleTag($tagNames, $articleId)
    {
        $this->db->begin();

        $delIds = $this->deleteArticleTagSoft($articleId);
        if (false === $delIds) {
            $this->db->rollback();
            return false;
        }

        $tags = $this->getTagsByNames($tagNames);
        $arr = [];
        $tagTmp = [];
        foreach ($tags as $tag) {
            $tagTmp[] = $tag['name'];
        }
        foreach ($tagNames as $tagName) {
            if (!in_array($tagName, $tagTmp)) {
                $arr[] = $tagName;
            }
        }
        if (count($arr) > 0) {
            $this->insertList($arr);
        }
        $tags = $this->getTagsByNames($tagNames);


        $total = $this->getArticleTagCountByAid($articleId);
        foreach ($tags as $key => $tag) {
            if ($key > ($total - 1)) {
                $sql = "insert into `article_tag` values (null,{$articleId},{$tag['id']})";
                $b = $this->db->execute($sql);
                if ($b === false) {
                    $this->db->rollback();
                    return false;
                }
            } else {
                $sql = "update `article_tag` set `tag_id`=? where `id`=?";
                $b = $this->db->execute($sql, [$tag['id'], $delIds[$key]]);
                if ($b === false) {
                    $this->db->rollback();
                    return false;
                }
            }
        }

        $this->db->commit();

        return true;
    }

    /**
     * 批量添加tag
     * @param $tagNames
     */
    public function insertList($tagNames)
    {
        if (count($tagNames) > 0) {
            $val = '';
            foreach ($tagNames as $key => $tagName) {
                if ($key > 0) {
                    $val .= ',';
                }
                $val .= "(null,'{$tagName}'," . mt_rand(0, 5) . ")";
            }
            $sql = "insert into `tag` values {$val}";
            $this->db->execute($sql);
        }
    }

    /**
     * 获取tag列表根据tag名称列表
     * @param $tagNames
     * @return array
     */
    public function getTagsByNames($tagNames)
    {
        $sql = "select `id`,`name`,`icon` from `tag` where `name` in (" . implode(',', array_fill(0, count($tagNames), '?')) . ")";
        $res = $this->db->query($sql, $tagNames);
        $res->setFetchMode(\PDO::FETCH_ASSOC);
        $tags = $res->fetchAll();
        return $tags;
    }

    /**
     * 获取用户tag
     * @param $userId
     * @return array|bool
     */
    public function getUserTags($userId)
    {
        $sql = "select `id` from `article` where user_id=?";
        $rs = $this->db->query($sql, [$userId]);
        $rs->setFetchMode(\PDO::FETCH_ASSOC);
        $res = $rs->fetchAll();
        foreach ($res as $val) {
            $articleIds[] = $val['id'];
        }

        if (count($articleIds) === 0) {
            return false;
        }

        $sql = "select t.name,t.icon,t.id,art.article_id from `article_tag` art left join `tag` t on art.tag_id=t.id where art.article_id in (" . implode(',', array_fill(0, count($articleIds), '?')) . ")";
        $rs = $this->db->query($sql, $articleIds);
        $rs->setFetchMode(\PDO::FETCH_ASSOC);
        $res = $rs->fetchAll();

        return $res;
    }
}
