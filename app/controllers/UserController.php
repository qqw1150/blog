<?php

namespace app\controllers;

use app\libraries\Captcha;
use app\models\domains\LoginForm;
use app\models\domains\RegisterForm;
use app\models\User;
use app\services\UserService;
use app\validations\LoginValidation;
use app\validations\RegisterValidation;

class UserController extends ControllerBase
{

    public function initialize()
    {
        parent::initialize();
        //加载静态资源
        $this->assets->addCss('assets/css/user.css'.$this->staticDebug());
    }

    /**
     * 生成验证码
     */
    public function generateCaptchaAction()
    {
        $captcha=new Captcha();
        $code=$captcha->getCode();
        $this->session->set('captcha',$code);
        $captcha->doimg();
        return false;
    }


    /**
     * 进入登录页
     */
    public function loginHtmlAction()
    {
    }

    /**
     * 进入注册页面
     */
    public function registerHtmlAction(){
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
    public function registerAction(RegisterForm $registerForm){
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
        $userService=$this->di->get('userService');

        $validation = new RegisterValidation();
        $messages=$validation->validate($registerForm->toArray());
        if(count($messages)){
            $this->flash->error(serialize($messages));
            $this->dispatcher->forward(['action'=>'registerHtml']);
        }

        if($userService->register($registerForm)){
            $this->flash->error('注册失败');
            $this->dispatcher->forward(['action' => 'registerHtml']);
        }

        return $this->response->redirect('index/index');
    }

    public function logoutAction(){
        $this->di->get('userService')->logout();
        return $this->response->redirect('index/index');
    }
}

