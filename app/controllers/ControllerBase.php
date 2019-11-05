<?php

namespace app\controllers;

use app\services\UserService;
use Phalcon\Mvc\Controller;

/**
 * Class ControllerBase
 * @property UserService $userService
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

        $baseList = $this->assets->collection('base');
        $baseList->addCss('https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css');
        $baseList->addJs('assets/base/js/jquery-3.3.1.slim.min.js');
        $baseList->addJs('https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/js/bootstrap.min.js');
        $baseList->addJs('assets/base/js/common.js'.$this->staticDebug());
        $this->view->setTemplateAfter('common');
    }
}
