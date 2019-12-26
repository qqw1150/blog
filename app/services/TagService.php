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
     * 添加用户标签
     * @param $tagNames
     * @param $articleId
     * @return bool
     */
    public function addArticleTags($tagNames, $articleId)
    {
        $tags = $this->addTags($tagNames);

        $val = "";
        foreach ($tags as $key => $tag) {
            if ($key > 0) {
                $val .= ",";
            }
            $val .= "(null,{$articleId},{$tag['id']})";
        }
        $sql = "insert into article_tag(id,article_id,tag_id) values {$val}";
        $b = $this->db->execute($sql);
        if ($b === false) {
            return false;
        }

        return true;
    }


    /**
     * 添加标签
     * @param $tagNames
     * @param bool $return
     * @return array
     */
    public function addTags($tagNames)
    {
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
        return $tags;

    }

    /**
     * 修改文章tag
     * @param $tagNames
     * @param $articleId
     * @return bool
     */
    public function updateArticleTags($tagNames, $articleId)
    {
        $this->db->begin();

        $tags = $this->addTags($tagNames);
        $distIds = [];
        foreach ($tags as $val) {
            $distIds[] = $val['id'];
        }

        $sql = "select tag_id from article_tag where article_id=?";
        $rs = $this->db->query($sql, [$articleId]);
        $rs->setFetchMode(\PDO::FETCH_ASSOC);
        $res = $rs->fetchAll();
        $srcIds = [];
        foreach ($res as $val) {
            $srcIds[] = $val['tag_id'];
        }

        $t1 = array_values(array_diff($srcIds, $distIds));
        $t2 = array_values(array_diff($distIds, $srcIds));

        if (count($t1) > 0) {
            foreach ($t1 as $val) {
                $sql = "delete from article_tag where tag_id=?";
                $b = $this->db->execute($sql, [$val]);
                if ($b === false) {
                    $this->db->rollback();
                    return false;
                }
            }
        }

        if (count($t2) > 0) {
            $values = '';
            foreach ($t2 as $key => $val) {
                if ($key > 0) {
                    $values .= ',';
                }
                $values .= "(null,{$articleId},{$val})";
            }
            $sql = "insert into article_tag(id,article_id,tag_id) values {$values}";
            $b = $this->db->execute($sql);
            if ($b === false) {
                $this->db->rollback();
                return false;
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

        $sql = "select distinct t.name,t.icon,t.id from `article_tag` art left join `tag` t on art.tag_id=t.id where art.article_id in (" . implode(',', array_fill(0, count($articleIds), '?')) . ")";
        $rs = $this->db->query($sql, $articleIds);
        $rs->setFetchMode(\PDO::FETCH_ASSOC);
        $res = $rs->fetchAll();

        return $res;
    }

    public function getTagHtml($name, $icon)
    {
        switch ($icon) {
            case 1:
                $type = 'primary';
                break;
            case 2:
                $type = 'success';
                break;
            case 3:
                $type = 'info';
                break;
            case 4:
                $type = 'warning';
                break;
            case 5:
                $type = 'danger';
                break;
            default:
                $type = 'default';
        }

        $html = "<div class='tag'><label for='tag_{$name}' class='label label-{$type}'>{$name}</label><input type='checkbox' name='tags' id='tag_{$name}' value='{$name}'/></div>";
        return $html;
    }

    public function getTagHtmlV2($name, $icon)
    {
        switch ($icon) {
            case 1:
                $type = 'primary';
                break;
            case 2:
                $type = 'success';
                break;
            case 3:
                $type = 'info';
                break;
            case 4:
                $type = 'warning';
                break;
            case 5:
                $type = 'danger';
                break;
            default:
                $type = 'default';
        }

        $html = '<span class="label label-'.$type.'">'.$name.'</span>';
        return $html;
    }

    public function listAll()
    {
        $sql = "select id,name,icon from tag";
        $rs = $this->db->query($sql);
        $rs->setFetchMode(\PDO::FETCH_ASSOC);
        $tags = $rs->fetchAll();
        if(!empty($tags)){
            foreach ($tags as &$tag){
                $tag['html']=$this->getTagHtmlV2($tag['name'],$tag['icon']);
            }
        }
        return $tags;
    }
}
