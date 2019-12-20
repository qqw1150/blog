<?php

namespace app\controllers;

use app\libraries\CryptUtil;
use app\libraries\Page;
use app\services\UserService;
use app\services\ArticleService;
use app\models\domains\WriteArticleForm;
use Phalcon\Crypt\Mismatch;

/**
 * Class IndexController
 * @package app\controllers
 */
class IndexController extends ControllerBase
{

    public function indexAction()
    {
        //加载静态资源
        $this->assets->addCss('assets/css/index.css' . $this->staticDebug());
        $this->assets->addJs('assets/js/index.js' . $this->staticDebug());

        $p = $this->dispatcher->getParam('p', 'int!', 1);
        $page = new Page($p);
        $page = $this->articleService->list($page);
        $this->view->setVar('page',$page);
    }
    public function showArticleAction(){
        $this->assets->addCss('assets/css/index.css' . $this->staticDebug());
        $this->assets->addCss('assets/base/css/railscasts.min.css' . $this->staticDebug());
        $this->assets->addJs('assets/js/index.js' . $this->staticDebug());
        $this->assets->addJs('assets/plugs/ckeditor/plugins/codesnippet/lib/highlight/highlight.pack.js' . $this->staticDebug());

        $articleId=CryptUtil::num_decrypt($this->dispatcher->getParam('articleId','string',''));

        if($articleId!==""){
            $article = $this->articleService->getOne($articleId);
            $article['content'] = $this->articleService->formatContent($article['content']);
            $this->view->setVar('article',$article);
        }else{
            $this->flashSession->error("文章不存在");
            $this->goBack();
        }
    }


}

