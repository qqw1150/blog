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
use Phalcon\Logger\Adapter\File;

class ArticleService extends BaseService
{
    /**
     * 保存文章（创建或修改）
     * @param ArticleForm $articleForm
     * @return bool
     */
    public function save($articleForm)
    {
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
            return false;
        }

        return true;
    }

}