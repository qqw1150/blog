<?php
/**
 * Created by PhpStorm.
 * User: 14521
 * Date: 2019/10/11
 * Time: 13:38
 */

namespace app\models\domains;

use app\services\UserService;

/**
 * Class LoginForm
 * @property string $account
 * @property string $password
 * @property int $accountType
 * @property boolean $rememberMe
 * @package app\models\domains
 */
class LoginForm extends Base
{
    protected $account;
    protected $password;
    protected $accountType;
    protected $rememberMe;

    /**
     * @return boolean
     */
    public function getRememberMe()
    {
        return $this->rememberMe;
    }

    /**
     * @param boolean $rememberMe
     */
    public function setRememberMe($rememberMe)
    {
        $this->rememberMe = $rememberMe;
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
     * @return array
     */
    public function toArray()
    {
        return parent::_toArray(self::class);
    }
}