<?php
/**
 * Created by PhpStorm.
 * User: 14521
 * Date: 2019/10/28
 * Time: 14:56
 */

namespace app\services;

use app\exceptions\NotLoginException;
use app\libraries\Page;
use app\models\Article;
use app\models\domains\WriteArticleForm;
use Phalcon\Db\Adapter\Pdo\Mysql;

class ArticleService extends BaseService
{
    /**
     * 保存文章（创建或修改）
     * @param WriteArticleForm $articleForm
     * @return bool
     * @throws NotLoginException
     * @throws \Phalcon\Crypt\Mismatch
     */
    public function save($articleForm)
    {
        /**
         * @var Mysql $db
         */
        $db = $this->di->get('db');
        $db->begin();

        if ($articleForm->getId()) {
            $article = Article::findFirst($articleForm->getId());
            $isNew = false;
        } else {
            $article = new Article();
            $isNew = true;
        }

        $article->setTitle($articleForm->getTitle());
        $article->setContent($articleForm->getContent());

        /**
         * @var UserService $userService
         */
        $userService = $this->di->get('userService');
        $user = $userService->getLoginedUser();
        if ($user === false) {
            throw new NotLoginException();
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
        $tagNames = $articleForm->getTags();
        if ($isNew && count($tagNames) > 0) {
            $b = $tagService->addArticleTags($tagNames, $article->getId());
        } else {
            $b = $tagService->updateArticleTags($tagNames, $article->getId());
        }
        if ($b === false) {
            $db->rollback();
            return false;
        }

        $db->commit();

        return true;
    }

    /**
     * 获取用户文章
     * @param Page $page
     * @param int $userId
     * @return Page
     */
    public function listOfUser($page, $userId)
    {
        $index = $page->getIndex();
        $rows = $page->getPageSize();
        $sql = "select id,title,content,user_id,ctime from blog.article where user_id=? order by id desc limit {$index},{$rows}";
        $rs = $this->db->query($sql, [$userId]);
        $rs->setFetchMode(\PDO::FETCH_ASSOC);
        $articles = $rs->fetchAll();

        $sql = "select count(id) from blog.article where user_id=?";
        $rs = $this->db->query($sql, [$userId]);
        $rs->setFetchMode(\PDO::FETCH_NUM);
        $totalRes = $rs->fetchArray();
        $page->setTotalItems($totalRes[0]);
        $page->setItems($articles);
        return $page;
    }


}