<?php
/**
 * @var \Phalcon\Mvc\Router $router
 * @var \Phalcon\Di $di;
 */
$router = $di->getRouter();

// Define your routes here

##################### 用户路由 #####################
$router->add("/user.html",[
    'controller'=>'user',
    'action'=>'articleList',
],['GET']);
$user=new \Phalcon\Mvc\Router\Group(['controller'=>'user']);
$user->setPrefix('/user');
$user->add('/write.html',['action'=>'toWrite'],['GET']);
$user->add('/write',['action'=>'writeArticle'],['POST']);
$router->mount($user);




$router->handle();
