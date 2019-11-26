<?php
/**
 * Created by PhpStorm.
 * User: 14521
 * Date: 2019/10/28
 * Time: 14:56
 */

namespace app\services;


use app\models\Article;
use app\models\domains\ArticleForm;
use app\models\Tag;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Logger\Adapter\File;

class ArticleService extends BaseService
{
    /**
     * 保存文章（创建或修改）
     * @param ArticleForm $articleForm
     * @return bool
     * @throws \Phalcon\Crypt\Mismatch
     */
    public function save($articleForm)
    {
        /**
         * @var Mysql $db
         */
        $db=$this->di->get('db');
        $db->begin();

        if ($articleForm->getId()) {
            $article = Article::findFirst($articleForm->getId());
        } else {
            $article = new Article();
        }

        $article->setTitle($articleForm->getTitle());
        $article->setContent($articleForm->getContent());

        /**
         * @var UserService $userService
         */
        $userService = $this->di->get('userService');
        $user = $userService->getLoginedUser();
        if ($user === false) {
            return false;
        }
        $article->setUserId($user['id']);
        if ($article->save() === false) {
            $this->recordErr($article);
            $db->rollback();
            return false;
        }

        /**
         * 添加标签
         * @var TagService $tagService
         */
        $tagService = $this->di->get('tagService');
        $tagNames=$articleForm->getTags();
        foreach ($tagNames as $tagName){
            $b=$tagService->addUserTag($tagName,$user['id']);
            if($b===false){
                $db->rollback();
            }

            $b=$tagService->addArticleTag($tagName,$article->getId());
            if($b===false){
                $db->rollback();
            }
        }

        $db->commit();

        return true;
    }

}