<?php
/**
 * Created by PhpStorm.
 * User: 14521
 * Date: 2019/10/11
 * Time: 13:40
 */

namespace app\models\domains;

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
    protected $captcha='';
    protected $accountType;

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

    public function toArray()
    {
        return parent::_toArray(self::class);
    }


    public function fillData($data)
    {
        $this->setAccount($data['account']);
        $this->setPassword($data['password']);
        $this->setCaptcha($data['captcha']);
    }
}