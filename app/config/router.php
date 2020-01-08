<?php
/**
 * @var \Phalcon\Mvc\Router $router
 * @var \Phalcon\Di $di ;
 */
$router = $di->get('router');

// Define your routes here



##################### 普通路由 #####################
$router->add('/blog.html', ['action' => 'blog'], ['GET']);
$router->add('/book.html', ['action' => 'blog'], ['GET']);
$router->add('/tool.html', ['action' => 'blog'], ['GET']);

##################### 博客路由 #####################
$blog = new \Phalcon\Mvc\Router\Group(['controller' => 'blog']);
$blog->setPrefix('/blog');
$blog->add('/list.html', ['action' => 'index'], ['GET']);
$blog->add('/list-([0-9]+).html', ['action' => 'index', 'p' => 1], ['GET']);
$blog->add('/([0-9]+)/list.html', ['action' => 'listByTag', 'tagId' => 1], ['GET']);
$blog->add('/([0-9]+)/list-([0-9]+).html', ['action' => 'listByTag', 'tagId' => 1, 'p' => 2], ['GET']);
$blog->add('/article-(.*?).html', ['action' => 'showArticle', 'articleId' => 1], ['GET']);
$blog->add('/add-comment.html', ['action' => 'writeComment'], ['POST']);
$blog->add('/comment/([0-9]+)/list.html', ['action' => 'getCommentsArticle', 'articleId' => 1], ['GET']);
$router->mount($blog);

##################### 用户路由 #####################
$router->add("/user(.html)?", [
    'controller' => 'user',
    'action' => 'articleList',
], ['GET']);
$user = new \Phalcon\Mvc\Router\Group(['controller' => 'user']);
$user->setPrefix('/user');
$user->add('/list.html', ['action' => 'articleList'], ['GET']);
$user->add('/list-([0-9]+).html', ['action' => 'articleList', 'p' => 1], ['GET']);
$user->add('/([0-9]+)/list.html', ['action' => 'listByTag', 'tagId' => 1], ['GET']);
$user->add('/([0-9]+)/list-([0-9]+).html', ['action' => 'listByTag', 'tagId' => 1, 'p' => 2], ['GET']);
$user->add('/drag.html', ['action' => 'articleList', 'p' => 1, 'drag' => true], ['GET']);
$user->add('/drag-([0-9]+).html', ['action' => 'articleList', 'p' => 1, 'drag' => true], ['GET']);
$user->add('/write.html', ['action' => 'toWrite'], ['GET']);
$user->add('/write', ['action' => 'writeArticle'], ['POST']);
$user->add('/update-([0-9]+).html', ['action' => 'toWrite', 'articleId' => 1], ['GET']);
$user->add('/update-([0-9]+)-([0-9]+).html', ['action' => 'toWrite', 'articleId' => 1, 'p' => 2], ['GET']);
$user->add('/login.html', ['action' => 'loginHtml'], ['GET']);
$user->add('/register.html', ['action' => 'registerHtml'], ['GET']);
$user->add('/del-([0-9]+).html', ['action' => 'deleteArticle', 'articleId' => 1], ['GET']);
$user->add('/del-([0-9]+)-([0-9]+).html', ['action' => 'deleteArticle', 'articleId' => 1, 'p' => 2], ['GET']);
$user->add('/public-([0-9]+)-([0-9]+).html', ['action' => 'publicArticle', 'articleId' => 1, 'p' => 2], ['GET']);
$user->add('/article-(.*?).html', ['action' => 'showArticle', 'articleId' => 1], ['GET']);
$user->add('/tags.html', ['action' => 'tagList'], ['GET']);
$user->add('/tags-([0-9]+).html', ['action' => 'tagList', 'p' => 1], ['GET']);
$router->mount($user);


$router->handle();
