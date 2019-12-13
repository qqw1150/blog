<?php

namespace app\controllers;

use app\exceptions\NotLoginException;
use app\libraries\Captcha;
use app\libraries\Page;
use app\models\domains\LoginForm;
use app\models\domains\RegisterForm;
use app\models\domains\WriteArticleForm;
use app\models\User;
use app\services\ArticleService;
use app\services\TagService;
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
            $this->flash->error('非法的请求方式');
            $this->dispatcher->forward(['action' => 'loginHtml']);
        }
        if ($this->request->isPost()) {
            if ($this->security->checkToken()) {
                $this->flash->error('无效的token');
                $this->dispatcher->forward(['action' => 'loginHtml']);
            }
        }
        $validation = new LoginValidation();
        $messages = $validation->validate($loginForm->toArray());
        if (count($messages)) {
            foreach ($messages as $message) {
                echo $message->getMessage();
            }
            $this->dispatcher->forward(['action' => 'loginHtml']);
        }

        /**
         * @var UserService $userService
         */
        $userService = $this->di->get('userService');
        $bool = $userService->login($loginForm);
        if ($bool === false) {
            $this->flash->error('用户名或密码不正确');
            $this->dispatcher->forward(['action' => 'loginHtml']);
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
            $this->flash->error('非法的请求方式');
            $this->dispatcher->forward(['action' => 'loginHtml']);
        }
        if ($this->request->isPost()) {
            if ($this->security->checkToken()) {
                $this->flash->error('无效的token');
                $this->dispatcher->forward(['action' => 'loginHtml']);
            }
        }

        /**
         * @var UserService $userService
         */
        $userService = $this->di->get('userService');

        $validation = new RegisterValidation();
        $messages = $validation->validate($registerForm->toArray());
        if (count($messages)) {
            $this->flash->error(serialize($messages));
            $this->dispatcher->forward(['action' => 'registerHtml']);
        }

        if ($userService->register($registerForm)) {
            $this->flash->error('注册失败');
            $this->dispatcher->forward(['action' => 'registerHtml']);
        }

        return $this->response->redirect('index/index');
    }

    public function logoutAction()
    {
        $this->di->get('userService')->logout();
        return $this->response->redirect('index/index');
    }

    /**
     * 跳转到写作页面
     */
    public function toWriteAction()
    {

    }

    /**
     * 用戶文章列表
     * @throws Mismatch
     */
    public function articleListAction()
    {
        $p=$this->request->get('p','!int',1);
        $page=new Page($p);
        $user=$this->userService->getLoginedUser();
        $page = $this->articleService->listOfUser($page,$user['id']);
        $this->view->setVar('page', $page);
    }

    /**
     * 写文章
     * @param WriteArticleForm $writeArticleForm
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
                $articleService = $this->di->get('articleService');
                $b = $articleService->save($writeArticleForm);
                if ($b == true) {
                    return $this->response->redirect('/user/articleList');
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
}

