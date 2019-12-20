<?php
/**
 * @var \Phalcon\Mvc\Router $router
 * @var \Phalcon\Di $di ;
 */
$router = $di->getRouter();

// Define your routes here

##################### 首页路由 #####################
$index=new \Phalcon\Mvc\Router\Group(['controller'=>'index']);
$index->setPrefix('/index');
$index->add('/list.html',['action'=>'index'],['GET']);
$index->add('/list-([0-9]+).html', ['action' => 'index','p'=>1], ['GET']);
$index->add('/article-(.*?).html',['action'=>'showArticle','articleId'=>1],['GET']);
$router->mount($index);

##################### 用户路由 #####################
$router->add("/user(.html)?", [
    'controller' => 'user',
    'action' => 'articleList',
], ['GET']);
$user = new \Phalcon\Mvc\Router\Group(['controller' => 'user']);
$user->setPrefix('/user');
$user->add('/list.html', ['action' => 'articleList'], ['GET']);
$user->add('/list-([0-9]+).html', ['action' => 'articleList','p'=>1], ['GET']);
$user->add('/drag.html', ['action' => 'articleList','p'=>1,'drag'=>true], ['GET']);
$user->add('/drag-([0-9]+).html', ['action' => 'articleList','p'=>1,'drag'=>true], ['GET']);
$user->add('/write.html', ['action' => 'toWrite'], ['GET']);
$user->add('/write', ['action' => 'writeArticle'], ['POST']);
$user->add('/update-([0-9]+).html', ['action' => 'toWrite', 'articleId' => 1], ['GET']);
$user->add('/update-([0-9]+)-([0-9]+).html', ['action' => 'toWrite', 'articleId' => 1,'p'=>2], ['GET']);
$user->add('/login.html', ['action' => 'loginHtml'], ['GET']);
$user->add('/register.html', ['action' => 'registerHtml'], ['GET']);
$user->add('/del-([0-9]+).html',['action'=>'deleteArticle','articleId'=>1],['GET']);
$user->add('/del-([0-9]+)-([0-9]+).html',['action'=>'deleteArticle','articleId'=>1,'p'=>2],['GET']);
$user->add('/public-([0-9]+)-([0-9]+).html',['action'=>'publicArticle','articleId'=>1,'p'=>2],['GET']);
$user->add('/article-(.*?).html',['action'=>'showArticle','articleId'=>1],['GET']);
$router->mount($user);


$router->handle();
