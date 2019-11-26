<?php

namespace app\controllers;

use app\services\UserService; 
use app\services\ArticleService; 
use app\models\domains\ArticleForm;

/**
 * Class IndexController
 * @package app\controllers
 */
class IndexController extends ControllerBase
{

    public function indexAction()
    {
        //加载静态资源
        $this->assets->addCss('assets/css/index.css'.$this->staticDebug());
        $this->assets->addJs('assets/js/index.js'.$this->staticDebug());
    }
    
    
    public function testAction()
    {
    	$af=new ArticleForm();
    	$af->setTitle("java");
    	$af->setContent("hello java");
    	$af->setTags("java,c++,android");

    	$articleService=$this->di->get('articleService');
    	$b=$articleService->save($af);  
    	var_dump($b);
    }

    public function testUpdateArticleAction()
    {
    	$af=new ArticleForm();
    	$af->setId(1);
    	$af->setTitle("go");
    	$af->setContent("go language");
    	$af->setTags("java,c++,go");

    	$articleService=$this->di->get('articleService');
    	$b=$articleService->save($af);  
    	var_dump($b);
    }
}

