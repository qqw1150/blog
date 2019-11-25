<?php

namespace app\controllers;

use app\services\UserService;

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



}

