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
    public function addUserTag($tagName,$userId){
        /**
         * @var Mysql $db
         */
        $db=$this->di->get('db');
        $db->begin();

        $tag=$this->getTagNotCreate($tagName);
        if($tag===false){
            $db->rollback();
            return false;
        }

        $userTag=new UserTag();
        $userTag->setUserId($userId);
        $userTag->setTagId($tag->getId());

        $db->commit();

        return true;
    }

    /**
     * 添加文章标签
     * @param $tagName
     * @param $articleId
     * @return bool
     */
    public function addArticleTag($tagName,$articleId){
        /**
         * @var Mysql $db
         */
        $db=$this->di->get('db');
        $db->begin();

        $tag=$this->getTagNotCreate($tagName);
        if($tag===false){
            $db->rollback();
            return false;
        }

        $articleTag=new ArticleTag();
        $articleTag->setArticleId($articleId);
        $articleTag->setTagId($tag->getId());

        $db->commit();

        return true;
    }

    /**
     * 获取标签不存在添加
     * @param $tagName
     * @return Tag|bool
     */
    public function getTagNotCreate($tagName){
        if($this->exist($tagName)===false){
            $tag=new Tag();
            $tag->setName($tagName);
            $tag->setIcon(mt_rand(0,5));
            if($tag->save()===false){
                $this->recordErr($tag);
                return false;
            }
        }else{
            $tag=Tag::findFirst(['name=?0','bind'=>[$tagName]]);
        }

        return $tag;
    }

    public function exist($tagName){
        $count=Tag::count(['name=?0','bind'=>[$tagName]]);
        if($count>0){
            return true;
        }else{
            return false;
        }
    }
}