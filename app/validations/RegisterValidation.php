<?php
/**
 * Created by PhpStorm.
 * User: 14521
 * Date: 2019/10/11
 * Time: 13:08
 */

namespace app\validations;


use app\models\User;
use app\services\UserService;
use Phalcon\Session\Adapter\Files;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;

class RegisterValidation extends Validation
{

    public function initialize()
    {
        $this->add(['account', 'password', 'captcha'], new Validation\Validator\PresenceOf([
            'message' => [
                'account' => '账号是必须的',
                'password' => '密码是必须的',
                'captcha' => '验证码是必须的'
            ]
        ]));

        $this->add(['confirmPass', 'password'], new Callback([
            'callback' => function ($data) {
                return $data['confirmPass'] == $data['password'];
            },
            'message' => '两次密码不一致',
        ]));

        //检查验证码
        $this->add('captcha', new Callback([
            'callback' => function ($data) {
                /**
                 * @var Files $session ;
                 */
                $session = $this->di->get('session');
                $srcCaptcha = $session->get('captcha');

                if (!empty($srcCaptcha) && strtoupper($srcCaptcha) !== strtoupper($data['captcha'])) {
                    return true;
                }

                return false;
            },
            'message' => '验证码不正确',
        ]));

        //检查账号是否是合法有效的（手机号或邮箱）
        $this->add('account', new Validation\Validator\Callback([
            'callback' => function ($data) {
                $accountType = UserService::getAccountType($data['account']);
                if ($accountType === User::ACCOUNT_INVALID) {
                    return false;
                } else {
                    return true;
                }
            },
            'message' => "账户无效"
        ]));

        //检查账号是否存在
        $this->add(['account', 'password'], new Validation\Validator\Callback([
            'callback' => function ($data) {
                $accountType = UserService::getAccountType($data['account']);
                $exist = UserService::exist($data['account'], $data['password'], $accountType);
                if ($exist) {
                    return true;
                } else {
                    return false;
                }
            },
            'message' => "账户已存在"
        ]));
    }

}