<?php
/**
 * Created by PhpStorm.
 * User: 14521
 * Date: 2019/10/10
 * Time: 15:49
 */

namespace app\validations\validators;


use Phalcon\Validation\Validator;

/**
 * Class PhoneValidator
 * @package app\validators
 */
class PhoneValidator extends Validator
{

    /**
     * Executes the validation
     *
     * @param \Phalcon\Validation $validation
     * @param string $attribute
     * @return bool
     */
    public function validate(\Phalcon\Validation $validation, $attribute)
    {
        // TODO: Implement validate() method.
        $phone=$validation->getValue($attribute);
        if(!preg_match("/1[35789]\d{9}/i",$phone)){
            return false;
        }

        return true;
    }
}