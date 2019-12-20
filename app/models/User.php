<?php

namespace app\models;

use app\validations\validators\PhoneValidator;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Email as EmailValidator;

class User extends Base
{
    const ACCOUNT_PHONE = 1; #手机类型账号
    const ACCOUNT_EMAIL = 2; #邮箱类型账号
    const ACCOUNT_INVALID = -1; #无效账号

    /**
     *
     * @var integer
     */
    private $id;

    /**
     *
     * @var string
     */
    private $email;

    /**
     *
     * @var string
     */
    private $phone;

    /**
     *
     * @var string
     */
    private $photo;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $token;

    /**
     * @var int
     */
    private $timeout;

    /**
     * @return int|string
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @param int $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return string
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * @param string $photo
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {

        $this->password = $password;
    }


    /**
     * Validations and business logic
     *
     * @return boolean
     */
    public function validation()
    {
        $validator = new Validation();

        if (!empty($this->email)) {
            $validator->add(
                'email',
                new EmailValidator(
                    [
                        'model' => $this,
                        'message' => '请输入正确的邮箱地址',
                    ]
                )
            );
        }

        if (!empty($this->phone)) {
            $validator->add('phone', new PhoneValidator([
                'model' => $this,
                'message' => '请输入正确的手机号码'
            ]));
        }

        return $this->validate($validator);
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("blog");
        $this->setSource("user");
        $this->hasMany('id', 'app\models\Article', 'user_id', ['alias' => 'Article']);
        $this->hasMany('id', 'app\models\Comment', 'user_id', ['alias' => 'Comment']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'user';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return User[]|User|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return User|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    /**
     * @param array $data
     * @return User|null
     * @throws \ReflectionException
     */
    public static function toObj($data)
    {
        return parent::_toObj($data, self::class);
    }
}
