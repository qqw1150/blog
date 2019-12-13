<?php

namespace app\controllers;

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
    }


    public function testAction()
    {
        $af = new WriteArticleForm();
        $af->setTitle("java");
        $af->setContent("hello java");
        $af->setTags("java,go,css");

        $articleService = $this->di->get('articleService');
        $b = $articleService->save($af);
        var_dump($b);
    }

    public function test2Action()
    {
        /**
         * @var UserService $us
         */
        $us = $this->di->get('tagService');
        $res=$us->getUserTags(1);
        echo json_encode($res);
    }


    public function test3Action()
    {
        $s = microtime(true);
        $af = new WriteArticleForm();
//        $af->setId(7);
        $af->setTitle("go");
        $af->setContent("c++ go");
        $af->setTags("html,java,ruby");

        /**
         * @var ArticleService $articleService
         */
        $articleService = $this->di->get('articleService');
        try {
            $b = $articleService->save($af);
        } catch (Mismatch $e) {
        }
        $e = microtime(true);
        var_dump($e - $s);
        var_dump($b);
    }
}

