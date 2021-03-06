<?php

namespace app\controllers;

use app\exceptions\NotLoginException;
use app\libraries\Captcha;
use app\libraries\Page;
use app\models\domains\LoginForm;
use app\models\domains\RegisterForm;
use app\models\domains\ArticleForm;
use app\services\ArticleService;
use app\services\UserService;
use app\validations\LoginValidation;
use app\validations\RegisterValidation;
use Phalcon\Crypt\Mismatch;

class UserController extends ControllerBase
{

    public function initialize()
    {
        parent::initialize();
        //加载静态资源
        $this->assets->addCss('assets/css/user.css' . $this->staticDebug());

        try {
            $user = $this->userService->getLoginedUser();
            $tags = $this->tagService->getUserTags($user['id']);
            $this->view->setVar('tags', $tags);
        } catch (Mismatch $e) {

        }
    }

    /**
     * 生成验证码
     */
    public function generateCaptchaAction()
    {
        $captcha = new Captcha();
        $code = $captcha->getCode();
        $this->session->set('captcha', $code);
        $captcha->doimg();
        return false;
    }


    /**
     * 进入登录页
     */
    public function loginHtmlAction()
    {
        $this->view->setVar('noLayout', true);
    }

    /**
     * 进入注册页面
     */
    public function registerHtmlAction()
    {
        $this->view->setVar('noLayout', true);
    }

    /**
     * 执行登录操作
     * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     */
    public function loginAction(LoginForm $loginForm)
    {
        if (!$this->request->isPost()) {
            $this->flashSession->error('非法的请求方式');
            return $this->response->redirect('/user/login.html');
        }

        if ($this->security->checkToken()) {
            $this->flashSession->error('无效的token');
            return $this->response->redirect('/user/login.html');
        }

        $validation = new LoginValidation();
        $messages = $validation->validate($loginForm->toArray());
        if (count($messages)) {
            foreach ($messages as $message) {
                $this->flashSession->error($message->getMessage());
            }
            return $this->response->redirect('/user/login.html');
        }

        /**
         * @var UserService $userService
         */
        $userService = $this->di->get('userService');
        $bool = $userService->login($loginForm);

        if ($bool === false) {
            $this->flashSession->error('用户名或密码不正确');
            return $this->response->redirect('/user/login.html');
        }

        return $this->response->redirect('index/index');
    }

    /**
     * 执行注册操作
     * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     */
    public function registerAction(RegisterForm $registerForm)
    {
        if (!$this->request->isPost()) {
            $this->flashSession->error('非法的请求方式');
            return $this->response->redirect('/user/register.html');
        }
        if ($this->request->isPost()) {
            if ($this->security->checkToken()) {
                $this->flashSession->error('无效的token');
                return $this->response->redirect('/user/register.html');
            }
        }

        /**
         * @var UserService $userService
         */
        $userService = $this->di->get('userService');

        $validation = new RegisterValidation();
        $messages = $validation->validate($registerForm->toArray());
        if (count($messages)) {
            foreach ($messages as $message) {
                $this->flashSession->error($message->getMessage());
                return $this->response->redirect('/user/register.html');
            }

        }

        if ($userService->register($registerForm)) {
            $this->flashSession->error('注册失败');
            return $this->response->redirect('/user/register.html');
        }

        return $this->response->redirect('index/index');
    }

    public function logoutAction()
    {
        $this->userService->logout();
        return $this->response->redirect('index/index');
    }

    /**
     * 跳转到写作页面
     * @throws Mismatch
     */
    public function toWriteAction()
    {
        $articleId = $this->dispatcher->getParam('articleId', 'int!', 0);
        $p = $this->dispatcher->getParam('p', 'int!', 1);
        if ($articleId !== 0) {
            $article = $this->articleService->getOne($articleId);
            $tagsStr = "";
            foreach ($article['tags'] as $key => $tag) {
                if ($key > 0) {
                    $tagsStr .= ",";
                }
                $tagsStr .= $tag['name'];
            }
            $article['tagsStr'] = $tagsStr;
            $this->view->setVar('article', $article);
            $this->view->setVar('p', $p);
        }

    }

    /**
     * 用戶文章列表
     * @throws Mismatch
     */
    public function articleListAction()
    {
        $p = $this->dispatcher->getParam('p', 'int!', 1);
        $drag = $this->dispatcher->getParam('drag', null, false);
        $page = new Page($p);
        $user = $this->userService->getLoginedUser();
        if ($user === false) {
            $this->response->redirect('/user/login.html');
        }

        if ($drag) {
            $page = $this->articleService->listOfUser($page, $user['id'], 1);
        } else {
            $page = $this->articleService->listOfUser($page, $user['id']);
        }

        $this->view->setVar('page', $page);
        $this->view->setVar('drag', $drag);
    }

    /**
     * 写文章
     * @param ArticleForm $writeArticleForm
     * @throws NotLoginException
     * @throws Mismatch
     */
    public function writeArticleAction($writeArticleForm)
    {
        /**
         * @var ArticleService $articleService
         */

        if ($this->request->isPost()) {
            if ($this->security->checkToken()) {
                $p = $this->request->getPost('p', 'int!', 1);
                $articleService = $this->di->get('articleService');
                $b = $articleService->save($writeArticleForm);
                if ($b === true) {
                    return $this->response->redirect('/user/list-' . $p . '.html');
                } else {
                    $this->flashSession->error("提交失败");
                }
            } else {
                $this->flashSession->error("提交失败");
            }
        } else {
            $this->flashSession->error("非法的请求方法");
        }

        return $this->response->redirect('/user.html');
    }

    public function deleteArticleAction()
    {
        $articleId = $this->dispatcher->getParam('articleId', 'int!', 0);
        $p = $this->dispatcher->getParam('p', 'int!', 1);

        if ($articleId !== 0) {
            $b = $this->articleService->delteSoft($articleId);
            if ($b === false) {
                $this->flashSession->error("删除失败");
            }

            $this->response->redirect('/user/list-' . $p . '.html');
        }
    }

    public function publicArticleAction()
    {
        $articleId = $this->dispatcher->getParam('articleId', 'int!', 0);
        $p = $this->dispatcher->getParam('p', 'int!', 1);

        if ($articleId !== 0) {
            $b = $this->articleService->pub($articleId);
            if ($b === false) {
                $this->flashSession->error("发布失败");
            }

            $this->response->redirect('/user/drag-' . $p . '.html');
        }
    }

    public function showArticleAction()
    {
        $this->dispatcher->forward([
            'controller' => 'blog',
            'action' => 'showArticle',
        ]);
    }

    public function tagListAction()
    {
        $p = $this->dispatcher->getParam('p', 'int!', 1);
        $page = new Page($p);
        $page->setPageSize(10);
        $page = $this->tagService->listAllPage($page);
        $this->view->setVar('page', $page);
    }

    public function saveTagAction()
    {
        $tagName = $this->request->getPost('tagName', 'trim', '');
        $oldName = $this->request->getPost('old', 'trim', '');
        $type = $this->request->getPost('type', 'int!', 0);
        $p = $this->dispatcher->getParam('p', 'int!', 1);
        if ($type !== 0 && $tagName !== "") {
            switch ($type) {
                case 1:
                    $b = $this->tagService->add($tagName);
                    if ($b === false) {
                        $this->flashSession->error("添加失败");
                    }else{
                        $this->flashSession->success("添加成功");
                    }
                    break;
                case 2:
                    $b = $this->tagService->update($tagName, $oldName);
                    if ($b === false) {
                        $this->flashSession->error("修改失败");
                    }else{
                        $this->flashSession->success("修改成功");
                    }
                    break;
            }
        } else {
            $this->flashSession->error("操作失败");
        }

        $this->response->redirect('/user/tags-' . $p . '.html');
    }

    public function listByTagAction(){
        $this->dispatcher->setParam('controller','user');
        return $this->dispatcher->forward([
            'controller'=>'blog',
            'action'=>'listByTag',
        ]);
    }
}

