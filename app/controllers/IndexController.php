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

    public function testAction(){
        $url='https://qm.qumingdashi.com/en2020/#/pages/middle/english';
        $p = [
            'fullname' => '看了',
            'sex' => 1,
            'constellation' => 1,
            'feature' => '6,7,8',
            'type' => 1,
        ];
        $url.='?'.http_build_query($p);
        $s = file_get_contents($url);
        echo $s;exit;
//        $url='https://qm.qumingdashi.com/newqiming/submit/english?search_referer_id=21294109';
        $c=curl_init($url);
        curl_setopt($c, CURLOPT_HTTPHEADER, [
            'Request URL: https://qm.qumingdashi.com/en2020/',
            'Request Method: GET',
            'Status Code: 304 Not Modified',
            'Remote Address: 119.23.14.220:443',
            'Referrer Policy: no-referrer-when-downgrade',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
            'Accept-Encoding: gzip, deflate, br',
            'Accept-Language: zh-CN,zh;q=0.9',
            'Cache-Control: max-age=0',
            'Connection: keep-alive',
            'Cookie: PHPSESSID=5a1a0572c795b620726fe0b679bc3ab7; search_referer_id=KSbgcnxH94IedvXQMSh5WgqrZQOCvMIy',
            'Host: qm.qumingdashi.com',
            'If-Modified-Since: Thu, 31 Oct 2019 06:21:27 GMT',
            'If-None-Match: W/"5dba7d67-53e"',
            'Referer: https://m.yw11.com/englisgname/',
            'Upgrade-Insecure-Requests: 1',
            'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1'
        ]);
//        curl_setopt($c,CURLOPT_POST,true);
//        curl_setopt($c,CURLOPT_POSTFIELDS,$p);
        curl_setopt($c,CURLOPT_RETURNTRANSFER,true);
        $r=curl_exec($c);
        var_dump($r,curl_error($c));exit;
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

