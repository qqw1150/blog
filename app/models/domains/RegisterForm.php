<?php
/**
 * Created by PhpStorm.
 * User: 14521
 * Date: 2019/10/11
 * Time: 13:40
 */

namespace app\models\domains;

use app\libraries\CommonUtil;
use app\services\UserService;

/**
 * Class RegisterForm
 * @property string $account;
 * @property string $password;
 * @property string $captcha;
 * @property int $accountType;
 * @package app\models\domains
 */
class RegisterForm extends Base
{
    protected $account='';
    protected $password='';
    protected $confirmPass='';
    protected $captcha='';
    protected $accountType;
    protected $nickname='';

    /**
     * @return string
     */
    public function getNickname(): string
    {
        return $this->nickname;
    }

    /**
     * @param string $nickname
     */
    public function setNickname(string $nickname): void
    {
        $this->nickname = $nickname;
    }

    /**
     * @return string
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param string $account
     */
    public function setAccount($account)
    {
        $this->account = $account;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return UserService::encryptPassword($this->password);
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getCaptcha()
    {
        return $this->captcha;
    }

    /**
     * @param string $captcha
     */
    public function setCaptcha($captcha)
    {
        $this->captcha = $captcha;
    }

    /**
     * @return int
     */
    public function getAccountType()
    {
        if (empty($this->accountType) && $this->getAccount()) {
            $accountType = UserService::getAccountType($this->getAccount());
            return $accountType ? $accountType : $this->accountType;
        }
        return $this->accountType;
    }

    /**
     * @param int $accountType
     */
    public function setAccountType($accountType)
    {
        $this->accountType = $accountType;
    }

    /**
     * @return string
     */
    public function getConfirmPass(): string
    {
        return $this->confirmPass;
    }

    /**
     * @param string $confirmPass
     */
    public function setConfirmPass(string $confirmPass): void
    {
        $this->confirmPass = $confirmPass;
    }

    public function toArray()
    {
        return parent::_toArray(self::class);
    }


    public function fillData($data)
    {
        if(empty($data['nickname'])){
            $data['nickname'] = CommonUtil::generateNickName();
        }
        $this->setAccount($data['account']);
        $this->setPassword($data['password']);
        $this->setConfirmPass($data['confirmPass']);
        $this->setCaptcha($data['captcha']);
        $this->setNickname($data['nickname']);
    }
}