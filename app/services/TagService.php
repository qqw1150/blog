<?php
/**
 * Created by PhpStorm.
 * User: 14521
 * Date: 2019/11/4
 * Time: 13:45
 */

namespace app\services;

use app\models\ArticleTag;
use app\models\Tag;
use app\models\UserTag;
use Phalcon\Db\Adapter\Pdo\Mysql;

class TagService extends BaseService
{
    /**
     * 添加用户标签
     * @param $tagName
     * @param $userId
     * @return bool
     */
    public function addUserTag($tagName, $userId)
    {
        /**
         * @var Mysql $db
         */
        $db = $this->di->get('db');
        $db->begin();

        $tag = $this->getTagNotCreate($tagName);
        if ($tag === false) {
            $db->rollback();
            return false;
        }

        if (false === $this->existUserTag($userId, $tag->getId())) {
            $userTag = new UserTag();
            $userTag->setUserId($userId);
            $userTag->setTagId($tag->getId());
            if ($userTag->save() === false) {
                $this->recordErr($userTag);
                $db->rollback();
                return false;
            }
        }

        $db->commit();

        return true;
    }

    /**
     * 用户标签是否存在
     * @param  int $userId
     * @param  int $tagId
     * @return bool
     */
    public function existUserTag($userId, $tagId)
    {
        $count = UserTag::count(['user_id=?0 and tag_id=?0', 'bind' => [$userId, $tagId], [Column::BIND_PARAM_INT, Column::BIND_PARAM_INT]]);

        return intval($count) > 0 ? true : false;
    }

    /**
     * 文章标签是否存在
     * @param  int $articleId
     * @param  int $tagId
     * @return bool
     */
    public function existArticleTag($articleId, $tagId)
    {
        $count = ArticleTag::count(['article_id=?0 and tag_id=?0', 'bind' => [$articleId, $tagId], [Column::BIND_PARAM_INT, Column::BIND_PARAM_INT]]);

        return intval($count) > 0 ? true : false;
    }

    /**
     * 添加文章标签
     * @param $tagName
     * @param $articleId
     * @return bool
     */
    public function addArticleTag($tagName, $articleId)
    {
        /**
         * @var Mysql $db
         */
        $db = $this->di->get('db');
        $db->begin();

        $tag = $this->getTagNotCreate($tagName);
        if ($tag === false) {
            $db->rollback();
            return false;
        }

        if (false === $this->existArticleTag($articleId, $tag->getId())) {
            $articleTag = new ArticleTag();
            $articleTag->setArticleId($articleId);
            $articleTag->setTagId($tag->getId());
            if ($articleTag->save() === false) {
                $this->recordErr($articleTag);
                $db->rollback();
                return false;
            }
        }

        $db->commit();

        return true;
    }

    /**
     * 获取标签不存在添加
     * @param $tagName
     * @return Tag|bool
     */
    public function getTagNotCreate($tagName)
    {
        if ($this->exist($tagName) === false) {
            $tag = new Tag();
            $tag->setName($tagName);
            $tag->setIcon(mt_rand(0, 5));
            if ($tag->save() === false) {
                $this->recordErr($tag);
                return false;
            }
        } else {
            $tag = Tag::findFirst(['name=?0', 'bind' => [$tagName]]);
        }

        return $tag;
    }

    public function exist($tagName)
    {
        $count = Tag::count(['name=?0', 'bind' => [$tagName]]);
        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }
}
