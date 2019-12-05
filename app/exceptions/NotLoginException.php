<?php

namespace app\exceptions;


use Phalcon\Exception;
use Throwable;

class NotLoginException extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        if($message===""){
            $message="未登录授权或授权失败";
        }

        parent::__construct($message, $code, $previous);
    }

}