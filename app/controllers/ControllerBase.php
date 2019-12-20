<?php

namespace app\controllers;

use app\models\Tag;
use app\services\ArticleService;
use app\services\TagService;
use app\services\UserService;
use Phalcon\Mvc\Controller;

/**
 * Class ControllerBase
 * @property UserService $userService
 * @property ArticleService $articleService
 * @property TagService $tagService
 * @property \Redis $redis
 * @package app\controllers
 */
class ControllerBase extends Controller
{
    /**
     * 静态资源不缓存访问
     * @return string
     */
    protected function staticDebug(){
        $config=$this->di->getConfig();
        if($config->application->debug){
            return '?v='.time();
        }else{
            return '';
        }
    }

    public function initialize()
    {
        $this->view->disableLevel(\Phalcon\Mvc\View::LEVEL_LAYOUT);
        $this->view->setTemplateAfter('common');
    }

    public function goBack(){
        $domain=$this->config->application->domain2;
        if(isset($_SERVER['HTTP_REFERER']) && preg_match("@https?://".$domain."@i",$_SERVER['HTTP_REFERER'])){
            return $this->response->redirect($_SERVER['HTTP_REFERER']);
        }else{
            return $this->response->redirect('/');
        }
    }

}
