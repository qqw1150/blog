<?php

namespace app\controllers;

use app\libraries\Page;

/**
 * Class IndexController
 * @package app\controllers
 */
class IndexController extends ControllerBase
{
    public function initialize()
    {
        parent::initialize();

        $tags = $this->tagService->listAll();
        $this->view->setVar('tags', $tags);
    }

    public function indexAction()
    {
        //加载静态资源
        $this->assets->addCss('assets/css/index.css' . $this->staticDebug());
        $this->assets->addJs('assets/js/index.js' . $this->staticDebug());

        $p = $this->dispatcher->getParam('p', 'int!', 1);
        $page = new Page($p);
        $page = $this->articleService->list($page);
        $this->view->setVar('page', $page);

        $tags = $this->tagService->listAll();
        $this->view->setVar('tags', $tags);
    }

    public function blogAction()
    {
        return $this->dispatcher->forward([
            'controller' => 'blog',
            'action' => 'index'
        ]);

    }

    public function bookAction()
    {
        $this->dispatcher->forward([
            'controller' => 'book',
        ]);
    }

    public function toolAction()
    {
        $this->dispatcher->forward([
            'controller' => 'tool',
        ]);
    }

    public function searchAction()
    {
        //加载静态资源
        $this->assets->addCss('assets/css/index.css' . $this->staticDebug());
        $this->assets->addJs('assets/js/index.js' . $this->staticDebug());

        $p = $this->request->get('p', 'int!', 1);
        $keyword = $this->request->get('keyword', 'trim', '');

        $page = new Page($p);
        $page = $this->articleService->search($page, $keyword);
        $this->view->setVar('page', $page);
        $this->view->setVar('keyword', $keyword);
    }

}

