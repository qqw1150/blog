<?php
/**
 * Created by PhpStorm.
 * User: 14521
 * Date: 2019/10/10
 * Time: 16:27
 */

namespace app\services;


use app\models\domains\LoginForm;
use app\models\domains\RegisterForm;
use app\models\User;
use Phalcon\Config;
use Phalcon\Crypt;
use Phalcon\Http\Response\Cookies;
use Phalcon\Logger\Adapter\File;
use Phalcon\Mvc\Model\Resultset\Simple;
use Phalcon\Session\Adapter\Files;
use Phalcon\Validation;

class UserService extends BaseService
{

    /**
     * @return bool|array
     */


    /**
     * 登录
     * @param LoginForm $loginForm ;
     * @return \Phalcon\Mvc\Model\ResultsetInterface|boolean
     * @throws \ReflectionException
     */
    public function login($loginForm)
    {
        if ($loginForm->getAccountType() === User::ACCOUNT_PHONE) {
            $accountWhere = 'phone=? and password=?';
        } else {
            $accountWhere = 'email=? and password=?';
        }

        $sql = "select id,email,phone,photo,nickname from user where {$accountWhere}";
        $rs = $this->db->query($sql, [$loginForm->getAccount(), $loginForm->getPassword()]);
        $rs->setFetchMode(\PDO::FETCH_ASSOC);
        $user = $rs->fetchArray();
        $userObj = User::toObj($user);

        if (empty($userObj)) {
            return false;
        }

        $selectRememberMe = $loginForm->getRememberMe();
        if ($selectRememberMe) {
            if ($this->rememberMe($userObj) == false) {
                return false;
            }
        }

        $this->saveUserSession($user);
        return true;
    }

    /**
     * 获取已登录用户
     * @return bool|\Phalcon\Mvc\Model\ResultsetInterface
     * @throws Crypt\Mismatch
     */
    public function getLoginedUser()
    {
        /**
         * @var Simple $user
         * @var Files $session
         */
        $session = $this->di->get('session');
        $user = $session->get('user');
        if (empty($user)) {
            $user = $this->checkIdentifier();
            if ($user === false) {
                return false;
            }
        }

        return $user;
    }

    /**
     * 用户是否存在
     * @param $account
     * @param $password
     * @param integer $type 账号类型 1:phone,2:email
     * @return bool
     */
    public static function exist($account, $password, $type)
    {
        if ($type === User::ACCOUNT_PHONE) {
            $accountWhere = 'phone=?0 and password=?1';
        } else {
            $accountWhere = 'email=?0 and password=?1';
        }

        $count = User::count([$accountWhere, 'bind' => [$account, $password]]);
        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * 注册
     * @param RegisterForm $registerForm
     * @return bool
     */
    public function register($registerForm)
    {
        $user = new User();
        $user->setPassword($registerForm->getPassword());
        if ($registerForm->getAccountType() === User::ACCOUNT_PHONE) {
            $user->setPhone($registerForm->getAccount());
        } else {
            $user->setEmail($registerForm->getAccount());
        }

        /**
         * @var File $logger
         */
        $logger = $this->di->get('logger');
        if ($user->save() === false) {
            $messages = $user->getMessages();
            foreach ($messages as $message) {
                $logger->error($message->getMessage() . '-' . $message->getType() . '-' . $message->getField());
            }
            return false;
        }

        return true;
    }

    /**
     * 获取账号类型
     * @param $account
     * @return int
     */
    public static function getAccountType($account)
    {
        if (preg_match("/1[35789]\d{9}/i", $account)) {
            return User::ACCOUNT_PHONE;
        }

        $validation = new Validation();
        $validation->add('email', new Validation\Validator\Email());
        $messages = $validation->validate(['email' => $account]);
        if (count($messages) === 0) {
            return User::ACCOUNT_EMAIL;
        }

        return User::ACCOUNT_INVALID;
    }

    /**
     * 加密用户密码（用于持久化）
     * @param string $password
     * @return string
     */
    public static function encryptPassword($password)
    {
        $solt = "ROOTroot123!@#";
        $password = md5($solt . $password . $solt);
        return $password;
    }

    /**
     * 记住密码
     * @param User $user
     * @return bool
     */
    public function rememberMe($user)
    {
        /**
         * @var Config $config
         * @var User $user ;
         * @var File $logger
         * @var Cookies $cookies
         * @var Crypt $crypt
         */
        $cookies = $this->di->get('cookies');
        $account = !empty($user->getPhone()) ? $user->getPhone() : $user->getEmail();
        $identifier = $this->getIdentifier($account);
        $token = $this->getToken();
        $expire = time() + 7200;
        $user->setIdentifier($identifier);
        $user->setToken($token);
        $user->setTimeout($expire);

        $sql = "update user set token=?,timeout=?,identifier=? where id=?";
        $b = $this->db->execute($sql, [$user->getToken(), $user->getTimeout(), $user->getIdentifier(), $user->getId()]);
        if ($b === false) {
            return false;
        }

        $crypt = $this->di->get('crypt');
        $msg = $crypt->encrypt(json_encode(['identifier' => $identifier, 'token' => $token]), $crypt->getKey());
        $config = $this->di->get('config');
        $cookies->set('auth', $msg, $expire, '/', false, $config->application->domain2)->send();

        return true;
    }

    /**
     * 检查免密登录的用户授权
     * @return bool
     * @throws Crypt\Mismatch
     */
    public function checkIdentifier()
    {
        /**
         * @var Crypt $crypt
         * @var Cookies $cookies
         */
        $crypt = $this->di->get('crypt');
        $cookies = $this->di->get('cookies');

        if ($cookies->has('auth')) {
            $auth = $cookies->get('auth')->getValue();
            $auth = $crypt->decrypt($auth, $crypt->getKey());
            $auth = json_decode($auth, true);
            if ($auth === false) {
                return false;
            }

            $identifier = $auth['identifier'];
            $token = $auth['token'];

            $sql = "select * from user where identifier=?";
            $rs = $this->db->query($sql, [$identifier]);
            $rs->setFetchMode(\PDO::FETCH_ASSOC);
            $user = $rs->fetchArray();

            if (empty($user)) {
                //不存在token身份
                return false;
            }


            if ($user['token'] != $token) {
                //token不正确
                return false;
            } else if (time() > $user['timeout']) {
                //token过期
                return false;
            }

            return $this->saveUserSession($user);
        }

        return false;
    }


    /**
     * 保持用户数据到session
     * @param array $user
     */
    public function saveUserSession($user)
    {
        /**
         * @var Files $session
         * @var TagService $tagService
         */
        $session = $this->di->get('session');
        $account = !empty($user['phone']) ? $user['phone'] : $user['email'];
        $accountType = self::getAccountType($account);

        $user['account'] = $account;
        $user['accountType'] = $accountType;
        $tagService = $this->di->get('tagService');
        $user['tags'] = $tagService->getUserTags($user['id']);
        $user['photo'] = self::getPhoto($user['photo']);
        foreach ($user['tags'] as &$tag) {
            $tag['html'] = $tagService->getTagHtml($tag['name'], intval($tag['icon']));
        }


        unset($user['token']);
        unset($user['password']);

        $session->set('user', $user);

        return $user;
    }


    public function getIdentifier($account)
    {
        $salt = "ROOTroot123!@#";
        return md5(md5($account) . $salt);
    }

    public function getToken()
    {
        return $token = md5(uniqid(rand(), TRUE));
    }

    public function logout()
    {
        /**
         * @var Files $session
         * @var Cookies $cookies
         */
        $session = $this->di->get('session');
        $cookies = $this->di->get('cookies');
        $session->remove('user');
        $session->destroy();

        $config = $this->di->get('config');
        $cookies->set('auth', '', '-1', '/', false, $config->application->domain2)->send();
        $cookies->get('auth')->delete();
    }

    /**
     * 获取用户图片
     * @param string $photo
     * @return string
     */
    public static function getPhoto(string $photo)
    {
        if (empty($photo)) {
            return STATIC_URL . '/image/default_user.jpg';
        } else {
            return STATIC_URL . '/image/' . $photo;
        }
    }
}