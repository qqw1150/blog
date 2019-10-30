<?php
/**
 * Created by PhpStorm.
 * User: 14521
 * Date: 2019/10/10
 * Time: 18:25
 */

namespace app\validations;


use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;

class LoginValidation extends Validation
{
    public function initialize(){
        //检查空值
        $this->add(['account','password'],new PresenceOf([
            "message" => [
                  "account"  => "账号是必须的",
                  "password" => "密码是必须的",
            ],
        ]));
    }
}