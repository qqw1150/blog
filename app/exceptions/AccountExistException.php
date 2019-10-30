<?php
/**
 * Created by PhpStorm.
 * User: 14521
 * Date: 2019/10/10
 * Time: 16:10
 */

namespace app\exceptions;


use Phalcon\Exception;
use Throwable;

class AccountExistException extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        if($message===""){
            $message="账号已经存在了";
        }

        parent::__construct($message, $code, $previous);
    }

}